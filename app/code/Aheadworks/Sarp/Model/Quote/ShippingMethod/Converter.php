<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Quote\ShippingMethod;

use Magento\Quote\Model\Quote\Address\RateResult\Method as RateResultMethod;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\Data\ShippingMethodInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Class Converter
 * @package Aheadworks\Sarp\Model\Quote\ShippingMethod
 */
class Converter
{
    /**
     * @var ShippingMethodInterfaceFactory
     */
    private $shippingMethodFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * @param ShippingMethodInterfaceFactory $shippingMethodFactory
     * @param StoreManagerInterface $storeManager
     * @param TaxHelper $taxHelper
     */
    public function __construct(
        ShippingMethodInterfaceFactory $shippingMethodFactory,
        StoreManagerInterface $storeManager,
        TaxHelper $taxHelper
    ) {
        $this->shippingMethodFactory = $shippingMethodFactory;
        $this->storeManager = $storeManager;
        $this->taxHelper = $taxHelper;
    }

    /**
     * Convert shipping rate result method to quote shipping method instance
     *
     * @param RateResultMethod $resultMethod
     * @return ShippingMethodInterface
     * @throws \Exception
     */
    public function fromRateResultMethod(RateResultMethod $resultMethod)
    {
        /** @var \Magento\Store\Api\Data\StoreInterface|\Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();
        $baseCurrency = $store->getBaseCurrency();
        $currentCurrency = $store->getCurrentCurrency();
        /** @var ShippingMethodInterface $shippingMethod */
        $shippingMethod = $this->shippingMethodFactory->create();
        $shippingMethod
            ->setCarrierCode($resultMethod->getCarrier())
            ->setMethodCode($resultMethod->getMethod())
            ->setCarrierTitle($resultMethod->getCarrierTitle())
            ->setMethodTitle($resultMethod->getMethodTitle())
            ->setAmount($baseCurrency->convert($resultMethod->getPrice(), $currentCurrency))
            ->setBaseAmount($resultMethod->getPrice())
            ->setPriceExclTax(
                $baseCurrency->convert(
                    $this->taxHelper->getShippingPrice($resultMethod->getPrice(), false),
                    $currentCurrency
                )
            )
            ->setPriceInclTax(
                $baseCurrency->convert(
                    $this->taxHelper->getShippingPrice($resultMethod->getPrice(), true),
                    $currentCurrency
                )
            );
        return $shippingMethod;
    }
}
