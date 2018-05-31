<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Converter;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class QuoteConverter
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Converter
 */
class QuoteConverter
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Convert subscription cart into quote instance
     *
     * @param SubscriptionsCartInterface $cart
     * @return Quote
     */
    public function convert(SubscriptionsCartInterface $cart)
    {
        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        /** @var StoreInterface|Store $store */
        $store = $this->storeManager->getStore($cart->getStoreId());
        $quote->setStore($store);

        return $quote;
    }
}
