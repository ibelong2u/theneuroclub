<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook\Handlers;

use Aheadworks\Sarp\Api\ProfileManagementInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterfaceFactory;
use Aheadworks\Sarp\Model\Profile\PaymentInfo;
use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Filter\ToUppercase;
use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook\HandlerInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class InvoicePaid
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook\Handlers
 */
class InvoicePaid implements HandlerInterface
{
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
     * @var ToUppercase
     */
    private $toUppercaseFilter;

    /**
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileManagementInterface $profileManagement
     * @param ProfilePaymentInfoInterfaceFactory $paymentInfoFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param ToUppercase $toUppercaseFilter
     */
    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        ProfileManagementInterface $profileManagement,
        ProfilePaymentInfoInterfaceFactory $paymentInfoFactory,
        PriceCurrencyInterface $priceCurrency,
        ToUppercase $toUppercaseFilter
    ) {
        $this->profileRepository = $profileRepository;
        $this->profileManagement = $profileManagement;
        $this->paymentInfoFactory = $paymentInfoFactory;
        $this->priceCurrency = $priceCurrency;
        $this->toUppercaseFilter = $toUppercaseFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(DataObject $eventObject)
    {
        $lines = $eventObject->getLines();
        $subscriptionId = $lines['data'][0]['id'];
        $profile = $this->profileRepository->getByReferenceId($subscriptionId);
        $this->profileManagement->createOrder($profile, $this->getPaymentInfo($profile, $eventObject));
    }

    /**
     * Get payment info
     *
     * @param ProfileInterface $profile
     * @param DataObject $eventObject
     * @return ProfilePaymentInfoInterface
     */
    private function getPaymentInfo($profile, $eventObject)
    {
        $baseCurrencyCode = $profile->getBaseCurrencyCode();
        $currencyCode = $this->toUppercaseFilter->filter($eventObject->getCurrency());
        /** @var Currency $currency */
        $currency = $this->priceCurrency->getCurrency($profile->getStoreId(), $currencyCode);

        $grandTotalAmount = $eventObject->getAmountDue() / 100;
        $taxAmount = $profile->getTaxAmount();
        $shippingAmount = $profile->getShippingAmount();
        $amount = $grandTotalAmount - $taxAmount - $shippingAmount;

        /** @var ProfilePaymentInfoInterface $paymentInfo */
        $paymentInfo = $this->paymentInfoFactory->create();
        $paymentInfo
            ->setPaymentType(PaymentInfo::PAYMENT_TYPE_REGULAR)
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
}
