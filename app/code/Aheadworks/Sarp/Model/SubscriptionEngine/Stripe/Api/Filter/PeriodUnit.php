<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Filter;

use Aheadworks\Sarp\Model\SubscriptionPlan\Source\BillingPeriod as PeriodUnitSource;

/**
 * Class PeriodUnit
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Filter
 */
class PeriodUnit implements \Zend_Filter_Interface
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        switch ($value) {
            case PeriodUnitSource::DAY:
                return 'day';
            case PeriodUnitSource::WEEK:
                return 'week';
            case PeriodUnitSource::MONTH:
                return 'month';
            case PeriodUnitSource::YEAR:
                return 'year';
            default:
                break;
        }
        return '';
    }
}
