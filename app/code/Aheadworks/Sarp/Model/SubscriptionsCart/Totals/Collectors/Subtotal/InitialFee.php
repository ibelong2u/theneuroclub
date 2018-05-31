<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Subtotal;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\CollectorInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ItemsRegistry;

/**
 * Class InitialFee
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Subtotal
 */
class InitialFee implements CollectorInterface
{
    /**
     * @var ItemsRegistry
     */
    private $addressItemsRegistry;

    /**
     * @param ItemsRegistry $addressItemsRegistry
     */
    public function __construct(ItemsRegistry $addressItemsRegistry)
    {
        $this->addressItemsRegistry = $addressItemsRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(
        SubscriptionsCartInterface $cart,
        SubscriptionsCartAddressInterface $address,
        SubscriptionsCartTotalsInterface $totals
    ) {
        $baseInitialFee = 0;
        $initialFee = 0;

        foreach ($this->addressItemsRegistry->retrieve($address, $cart) as $item) {
            if (!$item->getIsDeleted()) {
                $baseInitialFee += (float)$item->getBaseInitialFee() * $item->getQty();
                $initialFee += (float)$item->getInitialFee() * $item->getQty();
            }
        }

        $totals
            ->setBaseInitialFee($baseInitialFee)
            ->setInitialFee($initialFee);
    }
}
