<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\ShippingEstimationInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Model\Quote\ShippingMethod\Converter as ShippingMethodConverter;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ConverterManager as AddressConverterManager;
use Aheadworks\Sarp\Model\SubscriptionsCart\Shipping\RatesCollector;
use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Class ShippingEstimation
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 */
class ShippingEstimation implements ShippingEstimationInterface
{
    /**
     * @var RatesCollector
     */
    private $ratesCollector;

    /**
     * @var SubscriptionsCartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $customerAddressRepository;

    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @var AddressConverterManager
     */
    private $addressConverterManager;

    /**
     * @var ShippingMethodConverter
     */
    private $shippingMethodConverter;

    /**
     * @param RatesCollector $ratesCollector
     * @param SubscriptionsCartRepositoryInterface $cartRepository
     * @param AddressRepositoryInterface $customerAddressRepository
     * @param TotalsCollector $totalsCollector
     * @param AddressConverterManager $addressConverterManager
     * @param ShippingMethodConverter $shippingMethodConverter
     */
    public function __construct(
        RatesCollector $ratesCollector,
        SubscriptionsCartRepositoryInterface $cartRepository,
        AddressRepositoryInterface $customerAddressRepository,
        TotalsCollector $totalsCollector,
        AddressConverterManager $addressConverterManager,
        ShippingMethodConverter $shippingMethodConverter
    ) {
        $this->ratesCollector = $ratesCollector;
        $this->cartRepository = $cartRepository;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->totalsCollector = $totalsCollector;
        $this->addressConverterManager = $addressConverterManager;
        $this->shippingMethodConverter = $shippingMethodConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function estimate(
        $cartId,
        SubscriptionsCartAddressInterface $shippingAddress
    ) {
        $shippingMethods = [];

        $cart = $this->cartRepository->getActive($cartId);
        $this->totalsCollector->collectAddressTotals($cart, $shippingAddress);
        $ratesResult = $this->ratesCollector->collect($shippingAddress, $cart);
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $rateMethod */
        foreach ($ratesResult->getAllRates() as $rateMethod) {
            $shippingMethods[] = $this->shippingMethodConverter->fromRateResultMethod($rateMethod);
        }

        return $shippingMethods;
    }

    /**
     * {@inheritdoc}
     */
    public function estimateByCustomerAddressId($cartId, $customerAddressId)
    {
        $customerAddress = $this->customerAddressRepository->getById($customerAddressId);
        $address = $this->addressConverterManager->fromCustomerAddress($customerAddress);
        $address
            ->setAddressType(Address::TYPE_SHIPPING)
            ->setCartId($cartId);
        return $this->estimate($cartId, $address);
    }
}
