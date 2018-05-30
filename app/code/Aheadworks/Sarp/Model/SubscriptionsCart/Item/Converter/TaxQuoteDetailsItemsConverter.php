<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Item\Converter;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Item\Converter\QuoteAddressItemsConverter;
use Magento\Quote\Model\Quote\Address\Item as QuoteAddressItem;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;

/**
 * Class TaxQuoteDetailsItemsConverter
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Item
 */
class TaxQuoteDetailsItemsConverter
{
    /**
     * @var QuoteDetailsItemInterfaceFactory
     */
    private $taxQuoteDetailsItemFactory;

    /**
     * @var TaxClassKeyInterfaceFactory
     */
    private $taxClassKeyFactory;

    /**
     * @var QuoteAddressItemsConverter
     */
    private $quoteAddressItemsConverter;

    /**
     * @param QuoteDetailsItemInterfaceFactory $taxQuoteDetailsItemFactory
     * @param TaxClassKeyInterfaceFactory $taxClassKeyFactory
     * @param QuoteAddressItemsConverter $quoteAddressItemsConverter
     */
    public function __construct(
        QuoteDetailsItemInterfaceFactory $taxQuoteDetailsItemFactory,
        TaxClassKeyInterfaceFactory $taxClassKeyFactory,
        QuoteAddressItemsConverter $quoteAddressItemsConverter
    ) {
        $this->taxQuoteDetailsItemFactory = $taxQuoteDetailsItemFactory;
        $this->taxClassKeyFactory = $taxClassKeyFactory;
        $this->quoteAddressItemsConverter = $quoteAddressItemsConverter;
    }

    /**
     * Convert into tax quote details item instances
     *
     * @param SubscriptionsCartItemInterface[] $items
     * @param bool $isPriceIncludesTax
     * @param bool $isUseBaseCurrency
     * @param bool $isTrial
     * @return QuoteDetailsItemInterface[]
     */
    public function convert(
        array $items,
        $isPriceIncludesTax,
        $isUseBaseCurrency,
        $isTrial = false
    ) {
        $taxQuoteDetailsItems = [];
        $quoteAddressItems = $this->toQuoteAddressItems($items, $isTrial);
        foreach ($quoteAddressItems as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $parentTaxDetailsItem = $this->convertTaxQuoteDetailsItem(
                    $item,
                    $isPriceIncludesTax,
                    $isUseBaseCurrency
                );
                $taxQuoteDetailsItems[] = $parentTaxDetailsItem;
                /** @var QuoteAddressItem $child */
                foreach ($item->getChildren() as $child) {
                    $childItemDataObject = $this->convertTaxQuoteDetailsItem(
                        $child,
                        $isPriceIncludesTax,
                        $isUseBaseCurrency,
                        $parentTaxDetailsItem->getCode()
                    );
                    $taxQuoteDetailsItems[] = $childItemDataObject;
                }
            } else {
                $taxDetailsItem = $this->convertTaxQuoteDetailsItem(
                    $item,
                    $isPriceIncludesTax,
                    $isUseBaseCurrency
                );
                $taxQuoteDetailsItems[] = $taxDetailsItem;
            }
        }

        return $taxQuoteDetailsItems;
    }

    /**
     * Convert quote address item into tax details item
     *
     * @param QuoteAddressItem $item
     * @param bool $isPriceIncludesTax
     * @param bool $isUseBaseCurrency
     * @param string $parentCode
     * @return QuoteDetailsItemInterface
     */
    private function convertTaxQuoteDetailsItem(
        QuoteAddressItem $item,
        $isPriceIncludesTax,
        $isUseBaseCurrency,
        $parentCode = null
    ) {
        /** @var QuoteDetailsItemInterface $taxQuoteDetailsItem */
        $taxQuoteDetailsItem = $this->taxQuoteDetailsItemFactory->create();
        $taxQuoteDetailsItem->setCode($item->getTaxCalculationItemId())
            ->setQuantity($item->getQty())
            ->setTaxClassKey(
                $this->taxClassKeyFactory->create()
                    ->setType(TaxClassKeyInterface::TYPE_ID)
                    ->setValue($item->getProduct()->getTaxClassId())
            )
            ->setIsTaxIncluded($isPriceIncludesTax)
            ->setType('product');

        if ($isUseBaseCurrency) {
            if (!$item->getBaseTaxCalculationPrice()) {
                $item->setBaseTaxCalculationPrice($item->getBasePrice());
            }
            $taxQuoteDetailsItem->setUnitPrice($item->getBaseTaxCalculationPrice());
        } else {
            if (!$item->getTaxCalculationPrice()) {
                $item->setTaxCalculationPrice($item->getPrice());
            }
            $taxQuoteDetailsItem->setUnitPrice($item->getTaxCalculationPrice());
        }
        $taxQuoteDetailsItem->setParentCode($parentCode);

        return $taxQuoteDetailsItem;
    }

    /**
     * Convert cart items into quote address items instances
     *
     * @param SubscriptionsCartItemInterface[] $items
     * @param bool $isTrial
     * @return QuoteAddressItem[]
     */
    private function toQuoteAddressItems(array $items, $isTrial = false)
    {
        $taxCalculationItemId = 0;
        $quoteAddressItems = $this->quoteAddressItemsConverter->convert($items, null, $isTrial);
        foreach ($quoteAddressItems as $addressItem) {
            $sequence = 'sequence-' . ++$taxCalculationItemId;
            $addressItem->setTaxCalculationItemId($sequence);
            $addressItem->getAssociatedSubscriptionItem()
                ->setTaxCalculationItemId($sequence);
        }
        return $quoteAddressItems;
    }
}
