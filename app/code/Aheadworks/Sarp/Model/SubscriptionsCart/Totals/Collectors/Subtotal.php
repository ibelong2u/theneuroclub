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
 * Class Subtotal
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors
 */
class Subtotal implements CollectorInterface
{
    /**
     * @var ItemsRegistry
     */
    private $addressItemsRegistry;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param ItemsRegistry $addressItemsRegistry
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ItemsRegistry $addressItemsRegistry,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->addressItemsRegistry = $addressItemsRegistry;
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
        $baseSubtotal = 0;
        $subtotal = 0;
        foreach ($this->addressItemsRegistry->retrieve($address, $cart) as $item) {
            if (!$item->getIsDeleted()) {
                $baseRegularPrice = (float)$item->getBaseRegularPrice();
                $regularPrice = $this->priceCurrency->convert($baseRegularPrice);

                $item
                    ->setRowTotal($regularPrice * $item->getQty())
                    ->setBaseRowTotal($baseRegularPrice * $item->getQty());

                $baseSubtotal += $item->getBaseRowTotal();
                $subtotal += $item->getRowTotal();
            }
        }

        $totals
            ->setBaseSubtotal($baseSubtotal)
            ->setSubtotal($subtotal);
    }
}
