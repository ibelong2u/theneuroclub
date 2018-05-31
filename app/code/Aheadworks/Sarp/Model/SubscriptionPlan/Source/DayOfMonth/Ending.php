<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionPlan\Source\DayOfMonth;

/**
 * Class Ending
 * @package Aheadworks\Sarp\Model\SubscriptionPlan\Source\DayOfMonth
 */
class Ending
{
    /**
     * Get ending for day of month value
     *
     * @param int $value
     * @return \Magento\Framework\Phrase
     */
    public function getEnding($value)
    {
        if ($value == 1) {
            return __('st');
        } elseif ($value == 2) {
            return __('nd');
        } elseif ($value == 3) {
            return __('rd');
        } else {
            return __('th');
        }
    }
}
