<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet;

use Aheadworks\Sarp\Api\ProfileManagementInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterfaceFactory;
use Aheadworks\Sarp\Model\Profile\PaymentInfo;
use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\SilentPost\Debugger;
use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\SilentPost\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class SilentPost
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SilentPost
{
    /**
     * Response codes
     */
    const RESPONSE_CODE_APPROVED = 1;
    const RESPONSE_CODE_DECLINED = 2;
    const RESPONSE_CODE_ERROR = 3;
    const RESPONSE_CODE_HELD_FOR_REVIEW = 4;

    /**
     * Response reason codes
     */
    const RESPONSE_REASON_CODE_APPROVED = 1;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var Validator
     */
    private $requestValidator;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

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
     * @param DataObjectFactory $dataObjectFactory
     * @param Validator $requestValidator
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileManagementInterface $profileManagement
     * @param ProfilePaymentInfoInterfaceFactory $paymentInfoFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param Debugger $debugger
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        Validator $requestValidator,
        ProfileRepositoryInterface $profileRepository,
        ProfileManagementInterface $profileManagement,
        ProfilePaymentInfoInterfaceFactory $paymentInfoFactory,
        PriceCurrencyInterface $priceCurrency,
        Debugger $debugger
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->requestValidator = $requestValidator;
        $this->profileRepository = $profileRepository;
        $this->profileManagement = $profileManagement;
        $this->paymentInfoFactory = $paymentInfoFactory;
        $this->priceCurrency = $priceCurrency;
        $this->debugger = $debugger;
    }

    /**
     * Process silent post request
     *
     * @param array $postData
     * @throws \Exception
     * @return void
     */
    public function process(array $postData)
    {
        $this->debugger->addDebugData('authozenet_silentpost', $postData);

        $request = $this->dataObjectFactory->create($postData);
        if ($this->requestValidator->isValid($request)) {
            try {
                $profile = $this->profileRepository->getByReferenceId($request->getXSubscriptionId());
                $this->profileManagement->createOrder($profile, $this->getPaymentInfo($profile, $request));
            } catch (\Exception $e) {
                $this->debugger
                    ->addDebugData('exception', $e->getMessage())
                    ->debug();
                throw $e;
            }
        }

        $this->debugger->debug();
    }

    /**
     * Get payment info
     *
     * @param ProfileInterface $profile
     * @param DataObject $request
     * @return ProfilePaymentInfoInterface
     */
    private function getPaymentInfo($profile, $request)
    {
        $baseCurrencyCode = $profile->getBaseCurrencyCode();
        $currencyCode = $profile->getProfileCurrencyCode();

        $baseGrandTotalAmount = $request->getXAmount();
        $paymentNum = $request->getXSubscriptionPaynum();
        if ($profile->getIsTrialPeriodEnabled()
            && $profile->getTrialSubtotal() > 0
            && $paymentNum < $profile->getTotalBillingCycles() + $profile->getTrialTotalBillingCycles()
        ) {
            $paymentType = PaymentInfo::PAYMENT_TYPE_TRIAL;
            $baseSubtotal = $profile->getBaseTrialSubtotal();
            $baseTaxAmount = $profile->getBaseTrialTaxAmount();
        } else {
            $paymentType = PaymentInfo::PAYMENT_TYPE_REGULAR;
            $baseSubtotal = $profile->getBaseSubtotal();
            $baseTaxAmount = $profile->getBaseTaxAmount();
        }
        $baseShippingAmount = $baseGrandTotalAmount - $baseSubtotal - $baseTaxAmount;

        /** @var ProfilePaymentInfoInterface $paymentInfo */
        $paymentInfo = $this->paymentInfoFactory->create();
        $paymentInfo
            ->setPaymentType($paymentType)
            ->setTransactionId($request->getXTransId())
            ->setAmount(
                $this->priceCurrency->convert($baseSubtotal, $profile->getStoreId(), $currencyCode)
            )
            ->setBaseAmount($baseSubtotal)
            ->setTaxAmount(
                $this->priceCurrency->convert($baseTaxAmount, $profile->getStoreId(), $currencyCode)
            )
            ->setBaseTaxAmount($baseTaxAmount)
            ->setShippingAmount(
                $this->priceCurrency->convert($baseShippingAmount, $profile->getStoreId(), $currencyCode)
            )
            ->setBaseShippingAmount($baseShippingAmount)
            ->setGrandTotal(
                $this->priceCurrency->convert($baseGrandTotalAmount, $profile->getStoreId(), $currencyCode)
            )
            ->setBaseGrandTotal($baseGrandTotalAmount)
            ->setBaseCurrencyCode($baseCurrencyCode)
            ->setCurrencyCode($currencyCode);

        return $paymentInfo;
    }
}
