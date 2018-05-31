<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Tax;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ItemsRegistry;
use Aheadworks\Sarp\Model\SubscriptionsCart\Item\ConverterManager as ItemsConverterManager;
use Aheadworks\Sarp\Model\SubscriptionsCart\ConverterManager as CartConverterManager;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\CollectorInterface;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Class Subtotal
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Tax
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Subtotal implements CollectorInterface
{
    /**
     * @var ItemsRegistry
     */
    private $addressItemsRegistry;

    /**
     * @var ItemsConverterManager
     */
    private $itemsConverterManager;

    /**
     * @var CartConverterManager
     */
    private $cartConverterManager;

    /**
     * @var TaxCalculationInterface
     */
    private $taxCalculation;

    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @param ItemsRegistry $addressItemsRegistry
     * @param ItemsConverterManager $itemsConverterManager
     * @param CartConverterManager $cartConverterManager
     * @param TaxCalculationInterface $taxCalculation
     * @param TaxConfig $taxConfig
     */
    public function __construct(
        ItemsRegistry $addressItemsRegistry,
        ItemsConverterManager $itemsConverterManager,
        CartConverterManager $cartConverterManager,
        TaxCalculationInterface $taxCalculation,
        TaxConfig $taxConfig
    ) {
        $this->addressItemsRegistry = $addressItemsRegistry;
        $this->itemsConverterManager = $itemsConverterManager;
        $this->cartConverterManager = $cartConverterManager;
        $this->taxCalculation = $taxCalculation;
        $this->taxConfig = $taxConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(
        SubscriptionsCartInterface $cart,
        SubscriptionsCartAddressInterface $address,
        SubscriptionsCartTotalsInterface $totals
    ) {
        $subtotal = 0;
        $baseSubtotal = 0;
        $taxAmount = 0;
        $baseTaxAmount = 0;

        $items = $this->addressItemsRegistry->retrieveInner($address, $cart);
        if ($items) {
            $isPriceIncludesTax = $this->taxConfig->priceIncludesTax();

            $quoteDetails = $this->cartConverterManager->toTaxQuoteDetails(
                $cart,
                $this->itemsConverterManager->toTaxQuoteDetailsItems($items, $isPriceIncludesTax, false)
            );
            $taxDetails = $this->taxCalculation->calculateTax($quoteDetails);
            $taxAmount = $taxDetails->getTaxAmount();
            $this->processItems($items, $taxDetails, false);
            $subtotal = $this->calculateSubtotal($items, false);

            $baseQuoteDetails = $this->cartConverterManager->toTaxQuoteDetails(
                $cart,
                $this->itemsConverterManager->toTaxQuoteDetailsItems($items, $isPriceIncludesTax, true)
            );
            $baseTaxDetails = $this->taxCalculation->calculateTax($baseQuoteDetails);
            $baseTaxAmount = $baseTaxDetails->getTaxAmount();
            $this->processItems($items, $baseTaxDetails, true);
            $baseSubtotal = $this->calculateSubtotal($items, true);
        }

        $totals
            ->setSubtotal($subtotal)
            ->setBaseSubtotal($baseSubtotal)
            ->setTaxAmount((float)$totals->getTaxAmount() + $taxAmount)
            ->setBaseTaxAmount((float)$totals->getBaseTaxAmount() + $baseTaxAmount);
    }

    /**
     * Process items
     *
     * @param SubscriptionsCartItemInterface[] $items
     * @param TaxDetailsInterface $taxDetails
     * @param bool $isUseBaseCurrency
     * @return void
     */
    private function processItems($items, $taxDetails, $isUseBaseCurrency)
    {
        $taxItemsDetails = $taxDetails->getItems();
        foreach ($items as $item) {
            if (isset($taxItemsDetails[$item->getTaxCalculationItemId()])) {
                /** @var TaxDetailsItemInterface $taxItem */
                $taxItem = $taxItemsDetails[$item->getTaxCalculationItemId()];
                if ($isUseBaseCurrency) {
                    $item
                        ->setBaseRegularPrice($taxItem->getPrice())
                        ->setBaseRegularPriceInclTax($taxItem->getPriceInclTax())
                        ->setBaseRowTotal($taxItem->getRowTotal())
                        ->setBaseRowTotalInclTax($taxItem->getRowTotalInclTax())
                        ->setBaseTaxAmount($taxItem->getRowTax());
                } else {
                    $item
                        ->setRegularPrice($taxItem->getPrice())
                        ->setRegularPriceInclTax($taxItem->getPriceInclTax())
                        ->setRowTotal($taxItem->getRowTotal())
                        ->setRowTotalInclTax($taxItem->getRowTotalInclTax())
                        ->setTaxAmount($taxItem->getRowTax());
                }
                $item->setTaxPercent($taxItem->getTaxPercent());
            }
        }
    }

    /**
     * Calculate subtotal
     *
     * @param SubscriptionsCartItemInterface[] $items
     * @param bool $isUseBaseCurrency
     * @return float
     */
    private function calculateSubtotal($items, $isUseBaseCurrency)
    {
        $subtotal = 0;
        foreach ($items as $item) {
            if (!$item->getIsDeleted() && !$item->getParentItemId()) {
                $subtotal += $isUseBaseCurrency ? $item->getBaseRowTotal() : $item->getRowTotal();
            }
        }
        return $subtotal;
    }
}
