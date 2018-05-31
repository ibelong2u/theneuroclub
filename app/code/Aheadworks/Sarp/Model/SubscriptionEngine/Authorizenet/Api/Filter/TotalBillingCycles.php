<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Filter;

/**
 * Class TotalBillingCycles
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Filter
 */
class TotalBillingCycles implements \Zend_Filter_Interface
{
    /**
     * Infinite total billing cycles value
     */
    const TOTAL_CYCLES_INFINITE = 9999;

    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        if ($value == 0 || $value > self::TOTAL_CYCLES_INFINITE) {
            return self::TOTAL_CYCLES_INFINITE;
        }
        return $value;
    }
}
