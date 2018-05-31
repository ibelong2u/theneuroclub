<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Item\ConverterManager as ItemConverterManager;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ItemsRegistry;
use Aheadworks\Sarp\Model\SubscriptionsCart\Shipping\RatesCollector;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\CollectorInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Shipping
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Shipping implements CollectorInterface
{
    /**
     * @var ItemsRegistry
     */
    private $addressItemsRegistry;

    /**
     * @var ItemConverterManager
     */
    private $itemConverterManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var RatesCollector
     */
    private $shippingRatesCollector;

    /**
     * @param ItemsRegistry $addressItemsRegistry
     * @param ItemConverterManager $itemConverterManager
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param RatesCollector $shippingRatesCollector
     */
    public function __construct(
        ItemsRegistry $addressItemsRegistry,
        ItemConverterManager $itemConverterManager,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        RatesCollector $shippingRatesCollector
    ) {
        $this->addressItemsRegistry = $addressItemsRegistry;
        $this->itemConverterManager = $itemConverterManager;
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        $this->shippingRatesCollector = $shippingRatesCollector;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(
        SubscriptionsCartInterface $cart,
        SubscriptionsCartAddressInterface $address,
        SubscriptionsCartTotalsInterface $totals
    ) {
        $addressQty = 0;
        $addressWeight = 0;
        $baseShippingAmount = 0;
        $shippingAmount = 0;

        $shippingMethodCode = $address->getShippingMethodCode();

        $items = $this->addressItemsRegistry->retrieve($address, $cart);
        foreach ($this->itemConverterManager->toQuoteAddressItems($items, $address) as $index => $addressItem) {
            if ($addressItem->getProduct()->isVirtual() || $addressItem->getParentItem()) {
                continue;
            }

            if ($addressItem->getHasChildren() && $addressItem->isShipSeparately()) {
                foreach ($addressItem->getChildren() as $child) {
                    if ($child->getProduct()->isVirtual()) {
                        continue;
                    }
                    $addressQty += $child->getTotalQty();

                    if (!$addressItem->getProduct()->getWeightType()) {
                        $rowWeight = $child->getWeight() * $child->getTotalQty();
                        $addressWeight += $rowWeight;
                        $addressItem->setRowWeight($rowWeight);
                    }
                }
                if ($addressItem->getProduct()->getWeightType()) {
                    $rowWeight = $addressItem->getWeight() * $addressItem->getQty();
                    $addressWeight += $rowWeight;
                    $addressItem->setRowWeight($rowWeight);
                }
            } else {
                if (!$addressItem->getProduct()->isVirtual()) {
                    $addressQty += $addressItem->getQty();
                }
                $rowWeight = $addressItem->getWeight() * $addressItem->getQty();
                $addressWeight += $rowWeight;
                $addressItem->setRowWeight($rowWeight);
            }
            $items[$index]->setRowWeight($addressItem->getRowWeight());
        }

        $address
            ->setQty($addressQty)
            ->setWeight($addressWeight);

        if ($shippingMethodCode) {
            $shippingMethod = '';
            $shippingDescription = '';
            $ratesResult = $this->shippingRatesCollector->collect($address, $cart);
            foreach ($ratesResult->getAllRates() as $rateMethod) {
                if ($shippingMethodCode == $rateMethod->getMethod()) {
                    $shippingMethod = $rateMethod->getCarrier() . '_' . $rateMethod->getMethod();
                    $shippingDescription = $rateMethod->getCarrierTitle() . '-' . $rateMethod->getMethodTitle();
                    $baseShippingAmount = $rateMethod->getPrice();
                    $shippingAmount = $this->priceCurrency->convert($rateMethod->getPrice());
                }
            }
            $cart
                ->setShippingMethod($shippingMethod)
                ->setShippingDescription($shippingDescription);
        }

        $totals
            ->setBaseShippingAmount($baseShippingAmount)
            ->setShippingAmount($shippingAmount);
    }
}
