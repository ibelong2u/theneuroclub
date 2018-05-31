<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter;

use Aheadworks\Sarp\Model\SubscriptionPlan\Source\BillingPeriod as PeriodUnitSource;

/**
 * Class PeriodUnit
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter
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
                return 'Day';
            case PeriodUnitSource::WEEK:
                return 'Week';
            case PeriodUnitSource::SEMI_MONTH:
                return 'SemiMonth';
            case PeriodUnitSource::MONTH:
                return 'Month';
            case PeriodUnitSource::YEAR:
                return 'Year';
            default:
                break;
        }
        return '';
    }
}
