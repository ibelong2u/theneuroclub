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
 * Class Trial
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Subtotal
 */
class Trial implements CollectorInterface
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
        $baseTrialSubtotal = 0;
        $trialSubtotal = 0;

        foreach ($this->addressItemsRegistry->retrieve($address, $cart) as $item) {
            if (!$item->getIsDeleted()) {
                $item
                    ->setTrialRowTotal((float)$item->getTrialPrice() * $item->getQty())
                    ->setBaseTrialRowTotal((float)$item->getBaseTrialPrice() * $item->getQty());

                $baseTrialSubtotal += $item->getBaseTrialRowTotal();
                $trialSubtotal += $item->getTrialRowTotal();
            }
        }

        $totals
            ->setBaseTrialSubtotal($baseTrialSubtotal)
            ->setTrialSubtotal($trialSubtotal);
    }
}
