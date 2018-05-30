<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\CollectorInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ItemsRegistry;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class RegularPrice
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors
 */
class RegularPrice implements CollectorInterface
{
    /**
     * @var ItemsRegistry
     */
    private $addressItemsRegistry;

    /**
     * @var SubscriptionPriceCalculator
     */
    private $priceCalculator;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param ItemsRegistry $addressItemsRegistry
     * @param SubscriptionPriceCalculator $priceCalculator
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ItemsRegistry $addressItemsRegistry,
        SubscriptionPriceCalculator $priceCalculator,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->addressItemsRegistry = $addressItemsRegistry;
        $this->priceCalculator = $priceCalculator;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(
        SubscriptionsCartInterface $cart,
        SubscriptionsCartAddressInterface $address,
        SubscriptionsCartTotalsInterface $totals
    ) {
        foreach ($this->addressItemsRegistry->retrieveInner($address, $cart) as $item) {
            if (!$item->getIsDeleted()) {
                $baseRegularPrice = $this->priceCalculator->getBaseRegularPrice(
                    $item,
                    $this->addressItemsRegistry->retrieveChild($address, $cart, $item->getItemId())
                );
                $regularPrice = $this->priceCurrency->convert($baseRegularPrice);
                $item
                    ->setBaseRegularPrice($baseRegularPrice)
                    ->setRegularPrice($regularPrice);
            }
        }
    }
}
