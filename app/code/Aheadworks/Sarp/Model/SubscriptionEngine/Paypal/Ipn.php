<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal;

use Aheadworks\Sarp\Api\ProfileManagementInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterfaceFactory;
use Aheadworks\Sarp\Model\Profile\PaymentInfo;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\ProfileStatusFromApi as ProfileStatusFilter;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Ipn\Debugger;
use Magento\Directory\Model\Currency;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Paypal\Model\IpnInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * Class Ipn
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal
 */
class Ipn implements IpnInterface
{
    /**
     * Transaction types
     */
    const TXN_TYPE_RECURRING_PAYMENT = 'recurring_payment';
    const TXN_TYPE_RECURRING_PAYMENT_PROFILE_CREATED = 'recurring_payment_profile_created';
    const TXN_TYPE_RECURRING_PAYMENT_PROFILE_CANCEL = 'recurring_payment_profile_cancel';
    const TXN_TYPE_RECURRING_PAYMENT_EXPIRED = 'recurring_payment_expired';
    const TXN_TYPE_RECURRING_PAYMENT_SKIPPED = 'recurring_payment_skipped';
    const TXN_TYPE_RECURRING_PAYMENT_SUSPENDED = 'recurring_payment_suspended';

    /**
     * Payment statuses
     */
    const PAYMENT_STATUS_COMPLETED = 'Completed';

    /**
     * @var DataObject
     */
    private $request;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var ProfileStatusFilter
     */
    private $profileStatusFilter;

    /**
     * @var ProfilePaymentInfoInterfaceFactory
     */
    private $paymentInfoFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Debugger
     */
    private $debugger;

    /**
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileManagementInterface $profileManagement
     * @param ProfileStatusFilter $profileStatusFilter
     * @param ProfilePaymentInfoInterfaceFactory $paymentInfoFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param Debugger $debugger
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        ProfileManagementInterface $profileManagement,
        ProfileStatusFilter $profileStatusFilter,
        ProfilePaymentInfoInterfaceFactory $paymentInfoFactory,
        PriceCurrencyInterface $priceCurrency,
        Debugger $debugger,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->profileRepository = $profileRepository;
        $this->profileManagement = $profileManagement;
        $this->profileStatusFilter = $profileStatusFilter;
        $this->debugger = $debugger;
        $this->paymentInfoFactory = $paymentInfoFactory;
        $this->priceCurrency = $priceCurrency;
        $this->request = $dataObjectFactory->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function processIpnRequest()
    {
        $this->debugger->addDebugData('ipn', $this->request->getData());

        $transactionType = $this->request->getTxnType();
        try {
            $profile = $this->profileRepository->getByReferenceId(
                $this->request->getRecurringPaymentId()
            );
            if ($transactionType == self::TXN_TYPE_RECURRING_PAYMENT) {
                $this->processRecurringPayment($profile);
            } elseif (in_array(
                $transactionType,
                [
                    self::TXN_TYPE_RECURRING_PAYMENT_PROFILE_CREATED,
                    self::TXN_TYPE_RECURRING_PAYMENT_PROFILE_CANCEL,
                    self::TXN_TYPE_RECURRING_PAYMENT_EXPIRED,
                    self::TXN_TYPE_RECURRING_PAYMENT_SKIPPED,
                    self::TXN_TYPE_RECURRING_PAYMENT_SUSPENDED
                ]
            )) {
                $this->updateProfileStatus($profile);
            }
        } catch (\Exception $e) {
            $this->debugger
                ->addDebugData('exception', $e->getMessage())
                ->debug();
            throw $e;
        }

        $this->debugger->debug();
    }

    /**
     * Process recurring payment
     *
     * @param ProfileInterface $profile
     * @return void
     */
    private function processRecurringPayment(ProfileInterface $profile)
    {
        if ($this->request->getPaymentStatus() == self::PAYMENT_STATUS_COMPLETED) {
            $this->profileManagement->createOrder($profile, $this->getPaymentInfo($profile));
        }
    }

    /**
     * Get payment info
     *
     * @param ProfileInterface $profile
     * @return ProfilePaymentInfoInterface
     */
    private function getPaymentInfo($profile)
    {
        $baseCurrencyCode = $profile->getBaseCurrencyCode();
        $currencyCode = $this->request->getCurrencyCode();
        /** @var Currency $currency */
        $currency = $this->priceCurrency->getCurrency($profile->getStoreId(), $currencyCode);

        $grandTotalAmount = $this->request->getMcGross();
        $taxAmount = $this->request->getTax();
        $shippingAmount = $this->request->getShipping();
        $amount = $grandTotalAmount - $taxAmount - $shippingAmount;

        $periodType = trim($this->request->getPeriodType());
        if ($periodType == 'Trial') {
            $paymentType = PaymentInfo::PAYMENT_TYPE_TRIAL;
        } elseif ($periodType == 'Regular') {
            $paymentType = PaymentInfo::PAYMENT_TYPE_REGULAR;
        } else {
            $paymentType = PaymentInfo::PAYMENT_TYPE_INITIAL;
        }

        /** @var ProfilePaymentInfoInterface $paymentInfo */
        $paymentInfo = $this->paymentInfoFactory->create();
        $paymentInfo
            ->setPaymentType($paymentType)
            ->setTransactionId($this->request->getTxnId())
            ->setAmount($amount)
            ->setBaseAmount(
                $currency->convert($amount, $baseCurrencyCode)
            )
            ->setTaxAmount($taxAmount)
            ->setBaseTaxAmount(
                $currency->convert($taxAmount, $baseCurrencyCode)
            )
            ->setShippingAmount($shippingAmount)
            ->setBaseShippingAmount(
                $currency->convert($shippingAmount, $baseCurrencyCode)
            )
            ->setGrandTotal($grandTotalAmount)
            ->setBaseGrandTotal(
                $currency->convert($grandTotalAmount, $baseCurrencyCode)
            )
            ->setBaseCurrencyCode($baseCurrencyCode)
            ->setCurrencyCode($currencyCode);

        return $paymentInfo;
    }

    /**
     * Update profile status
     *
     * @param ProfileInterface $profile
     * @return void
     */
    private function updateProfileStatus(ProfileInterface $profile)
    {
        $status = $this->profileStatusFilter->filter($this->request->getProfileStatus());
        $profile->setStatus($status);
        $this->profileRepository->save($profile);
    }
}
