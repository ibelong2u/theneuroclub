<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Item;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Item\Converter\QuoteAddressItemsConverter;
use Aheadworks\Sarp\Model\SubscriptionsCart\Item\Converter\TaxQuoteDetailsItemsConverter;
use Magento\Quote\Model\Quote\Address\Item as QuoteAddressItem;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;

/**
 * Class ConverterManager
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Item
 */
class ConverterManager
{
    /**
     * @var QuoteAddressItemsConverter
     */
    private $toQuoteAddressItemsConverter;

    /**
     * @var TaxQuoteDetailsItemsConverter
     */
    private $toTaxQuoteDetailsItemsConverter;

    /**
     * @param QuoteAddressItemsConverter $toQuoteAddressItemsConverter
     * @param TaxQuoteDetailsItemsConverter $toTaxQuoteDetailsItemsConverter
     */
    public function __construct(
        QuoteAddressItemsConverter $toQuoteAddressItemsConverter,
        TaxQuoteDetailsItemsConverter $toTaxQuoteDetailsItemsConverter
    ) {
        $this->toQuoteAddressItemsConverter = $toQuoteAddressItemsConverter;
        $this->toTaxQuoteDetailsItemsConverter = $toTaxQuoteDetailsItemsConverter;
    }

    /**
     * Convert subscription cart items into quote address items
     *
     * @param SubscriptionsCartItemInterface[] $items
     * @param SubscriptionsCartAddressInterface $address
     * @param bool $useTrialPrice
     * @return QuoteAddressItem[]
     */
    public function toQuoteAddressItems(array $items, $address = null, $useTrialPrice = false)
    {
        return $this->toQuoteAddressItemsConverter->convert($items, $address, $useTrialPrice);
    }

    /**
     * Convert subscription cart items into tax quote details item instances
     *
     * @param SubscriptionsCartItemInterface[] $items
     * @param bool $isPriceIncludesTax
     * @param bool $isUseBaseCurrency
     * @param bool $isTrial
     * @return QuoteDetailsItemInterface[]
     */
    public function toTaxQuoteDetailsItems(
        array $items,
        $isPriceIncludesTax,
        $isUseBaseCurrency,
        $isTrial = false
    ) {
        return $this->toTaxQuoteDetailsItemsConverter->convert(
            $items,
            $isPriceIncludesTax,
            $isUseBaseCurrency,
            $isTrial
        );
    }
}
