<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Totals;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;

/**
 * Interface CollectorInterface
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Totals
 */
interface CollectorInterface
{
    /**
     * Collect address totals
     *
     * @param SubscriptionsCartInterface $cart
     * @param SubscriptionsCartAddressInterface $address
     * @param SubscriptionsCartTotalsInterface $totals
     * @return CollectorInterface
     */
    public function collect(
        SubscriptionsCartInterface $cart,
        SubscriptionsCartAddressInterface $address,
        SubscriptionsCartTotalsInterface $totals
    );
}
