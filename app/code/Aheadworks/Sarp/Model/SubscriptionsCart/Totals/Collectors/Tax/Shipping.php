<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Tax;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ItemsRegistry;
use Aheadworks\Sarp\Model\SubscriptionsCart\ConverterManager as CartConverterManager;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\CollectorInterface;
use Aheadworks\Sarp\Model\Config;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Class Shipping
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Tax
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Shipping implements CollectorInterface
{
    /**
     * @var ItemsRegistry
     */
    private $addressItemsRegistry;

    /**
     * @var CartConverterManager
     */
    private $cartConverterManager;

    /**
     * @var QuoteDetailsItemInterfaceFactory
     */
    private $taxQuoteDetailsItemFactory;

    /**
     * @var TaxClassKeyInterfaceFactory
     */
    private $taxClassKeyFactory;

    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @var TaxCalculationInterface
     */
    private $taxCalculation;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ItemsRegistry $addressItemsRegistry
     * @param CartConverterManager $cartConverterManager
     * @param QuoteDetailsItemInterfaceFactory $taxQuoteDetailsItemFactory
     * @param TaxClassKeyInterfaceFactory $taxClassKeyFactory
     * @param TaxConfig $taxConfig
     * @param TaxCalculationInterface $taxCalculation
     * @param Config $config
     */
    public function __construct(
        ItemsRegistry $addressItemsRegistry,
        CartConverterManager $cartConverterManager,
        QuoteDetailsItemInterfaceFactory $taxQuoteDetailsItemFactory,
        TaxClassKeyInterfaceFactory $taxClassKeyFactory,
        TaxConfig $taxConfig,
        TaxCalculationInterface $taxCalculation,
        Config $config
    ) {
        $this->addressItemsRegistry = $addressItemsRegistry;
        $this->cartConverterManager = $cartConverterManager;
        $this->taxQuoteDetailsItemFactory = $taxQuoteDetailsItemFactory;
        $this->taxClassKeyFactory = $taxClassKeyFactory;
        $this->taxConfig = $taxConfig;
        $this->taxCalculation = $taxCalculation;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(
        SubscriptionsCartInterface $cart,
        SubscriptionsCartAddressInterface $address,
        SubscriptionsCartTotalsInterface $totals
    ) {
        $items = $this->addressItemsRegistry->retrieve($address, $cart);
        if ($items) {
            $quoteDetails = $this->cartConverterManager->toTaxQuoteDetails(
                $cart,
                [$this->getTaxShippingQuoteDetailsItem($totals, false)]
            );
            $taxDetails = $this->taxCalculation->calculateTax($quoteDetails);
            $shippingTaxDetails = $taxDetails->getItems()['shipping'];

            $baseQuoteDetails = $this->cartConverterManager->toTaxQuoteDetails(
                $cart,
                [$this->getTaxShippingQuoteDetailsItem($totals, true)]
            );
            $baseTaxDetails = $this->taxCalculation->calculateTax($baseQuoteDetails);
            $baseShippingTaxDetails = $baseTaxDetails->getItems()['shipping'];

            $totals
                ->setShippingAmount($shippingTaxDetails->getRowTotal())
                ->setBaseShippingAmount($baseShippingTaxDetails->getRowTotal());
            if ($this->config->isApplyTaxOnShippingAmount()) {
                $totals
                    ->setTaxAmount((float)$totals->getTaxAmount() + $shippingTaxDetails->getRowTax())
                    ->setBaseTaxAmount((float)$totals->getBaseTaxAmount() + $baseShippingTaxDetails->getRowTax());
            }
        }
    }

    /**
     * Get shipping quote details item
     *
     * @param SubscriptionsCartTotalsInterface $totals
     * @param bool $isUseBaseCurrency
     * @return QuoteDetailsItemInterface
     */
    private function getTaxShippingQuoteDetailsItem(SubscriptionsCartTotalsInterface $totals, $isUseBaseCurrency)
    {
        $unitPrice = $isUseBaseCurrency
            ? $totals->getBaseShippingAmount()
            : $totals->getShippingAmount();

        /** @var QuoteDetailsItemInterface $taxQuoteDetailsItem */
        $taxQuoteDetailsItem = $this->taxQuoteDetailsItemFactory->create();
        $taxQuoteDetailsItem
            ->setCode('shipping')
            ->setType('shipping')
            ->setQuantity(1)
            ->setUnitPrice($unitPrice)
            ->setTaxClassKey(
                $this->taxClassKeyFactory->create()
                    ->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($this->taxConfig->getShippingTaxClass())
            )
            ->setIsTaxIncluded($this->taxConfig->shippingPriceIncludesTax());
        return $taxQuoteDetailsItem;
    }
}
