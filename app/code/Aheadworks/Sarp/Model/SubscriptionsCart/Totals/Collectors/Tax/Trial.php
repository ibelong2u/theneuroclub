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
use Aheadworks\Sarp\Model\Config;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Class Trial
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Tax
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Trial implements CollectorInterface
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
     * @var Config
     */
    private $config;

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
     * @param Config $config
     * @param CartConverterManager $cartConverterManager
     * @param TaxCalculationInterface $taxCalculation
     * @param TaxConfig $taxConfig
     */
    public function __construct(
        ItemsRegistry $addressItemsRegistry,
        ItemsConverterManager $itemsConverterManager,
        Config $config,
        CartConverterManager $cartConverterManager,
        TaxCalculationInterface $taxCalculation,
        TaxConfig $taxConfig
    ) {
        $this->addressItemsRegistry = $addressItemsRegistry;
        $this->itemsConverterManager = $itemsConverterManager;
        $this->config = $config;
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
        $trialTaxAmount = 0;
        $baseTrialTaxAmount = 0;

        $items = $this->addressItemsRegistry->retrieveInner($address, $cart);
        if ($this->config->isApplyTaxOnTrialAmount() && $items) {
            $isPriceIncludesTax = $this->taxConfig->priceIncludesTax();

            $quoteDetails = $this->cartConverterManager->toTaxQuoteDetails(
                $cart,
                $this->itemsConverterManager->toTaxQuoteDetailsItems($items, $isPriceIncludesTax, false, true)
            );
            $taxDetails = $this->taxCalculation->calculateTax($quoteDetails);
            $trialTaxAmount = $taxDetails->getTaxAmount();
            $this->processItems($items, $taxDetails, false);
            $trialSubtotal = $this->calculateTrialSubtotal($items, false);

            $baseQuoteDetails = $this->cartConverterManager->toTaxQuoteDetails(
                $cart,
                $this->itemsConverterManager->toTaxQuoteDetailsItems($items, $isPriceIncludesTax, true, true)
            );
            $baseTaxDetails = $this->taxCalculation->calculateTax($baseQuoteDetails);
            $baseTrialTaxAmount = $baseTaxDetails->getTaxAmount();
            $this->processItems($items, $baseTaxDetails, true);
            $baseTrialSubtotal = $this->calculateTrialSubtotal($items, true);

            $totals
                ->setTrialSubtotal($trialSubtotal)
                ->setBaseTrialSubtotal($baseTrialSubtotal);
        }

        $totals
            ->setTrialTaxAmount($trialTaxAmount)
            ->setBaseTrialTaxAmount($baseTrialTaxAmount);
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
                        ->setBaseTrialPrice($taxItem->getPrice())
                        ->setBaseTrialPriceInclTax($taxItem->getPriceInclTax())
                        ->setBaseTrialRowTotal($taxItem->getRowTotal())
                        ->setBaseTrialRowTotalInclTax($taxItem->getRowTotalInclTax())
                        ->setBaseTrialTaxAmount($taxItem->getRowTax());
                } else {
                    $item
                        ->setTrialPrice($taxItem->getPrice())
                        ->setTrialPriceInclTax($taxItem->getPriceInclTax())
                        ->setTrialRowTotal($taxItem->getRowTotal())
                        ->setTrialRowTotalInclTax($taxItem->getRowTotalInclTax())
                        ->setTrialTaxAmount($taxItem->getRowTax());
                }
                $item->setTaxPercent($taxItem->getTaxPercent());
            }
        }
    }

    /**
     * Calculate trial subtotal
     *
     * @param SubscriptionsCartItemInterface[] $items
     * @param bool $isUseBaseCurrency
     * @return float
     */
    private function calculateTrialSubtotal($items, $isUseBaseCurrency)
    {
        $subtotal = 0;
        foreach ($items as $item) {
            if (!$item->getIsDeleted() && !$item->getParentItemId()) {
                $subtotal += $isUseBaseCurrency ? $item->getBaseTrialRowTotal() : $item->getTrialRowTotal();
            }
        }
        return $subtotal;
    }
}
