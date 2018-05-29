<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Converter\QuoteConverter;
use Aheadworks\Sarp\Model\SubscriptionsCart\Converter\TaxQuoteDetailsConverter;
use Magento\Quote\Model\Quote;
use Magento\Tax\Api\Data\QuoteDetailsInterface;

/**
 * Class ConverterManager
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 */
class ConverterManager
{
    /**
     * @var QuoteConverter
     */
    private $toQuoteConverter;

    /**
     * @var TaxQuoteDetailsConverter
     */
    private $toTaxQuoteDetailsConverter;

    /**
     * @param QuoteConverter $toQuoteConverter
     * @param TaxQuoteDetailsConverter $toTaxQuoteDetailsConverter
     */
    public function __construct(
        QuoteConverter $toQuoteConverter,
        TaxQuoteDetailsConverter $toTaxQuoteDetailsConverter
    ) {
        $this->toQuoteConverter = $toQuoteConverter;
        $this->toTaxQuoteDetailsConverter = $toTaxQuoteDetailsConverter;
    }

    /**
     * Convert subscription cart into quote instance
     *
     * @param SubscriptionsCartInterface $cart
     * @return Quote
     */
    public function toQuote(SubscriptionsCartInterface $cart)
    {
        return $this->toQuoteConverter->convert($cart);
    }

    /**
     * Convert subscription cart into tax quote details instance
     *
     * @param SubscriptionsCartInterface $cart
     * @param array $taxQuoteDetailsItems
     * @return QuoteDetailsInterface
     */
    public function toTaxQuoteDetails(SubscriptionsCartInterface $cart, array $taxQuoteDetailsItems)
    {
        return $this->toTaxQuoteDetailsConverter->convert($cart, $taxQuoteDetailsItems);
    }
}
