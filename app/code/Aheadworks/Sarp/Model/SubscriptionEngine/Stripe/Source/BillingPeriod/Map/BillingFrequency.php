<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Source\BillingPeriod\Map;

use Aheadworks\Sarp\Model\SubscriptionPlan\Source\BillingPeriod as BillingPeriodSource;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\DayOfMonth\Ending;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\MapInterface;

/**
 * Class BillingFrequency
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Source\BillingPeriod\Map
 */
class BillingFrequency implements MapInterface
{
    /**
     * @var Ending
     */
    private $ending;

    /**
     * @var array
     */
    private $optionMap;

    /**
     * @param Ending $ending
     */
    public function __construct(Ending $ending)
    {
        $this->ending = $ending;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionMapArray()
    {
        if (!$this->optionMap) {
            $this->optionMap = [
                BillingPeriodSource::DAY => $this->getOptionArray(1, 365),
                BillingPeriodSource::WEEK => $this->getOptionArray(1, 52),
                BillingPeriodSource::MONTH => $this->getOptionArray(1, 12),
                BillingPeriodSource::YEAR => $this->getOptionArray(1)
            ];
        }
        return $this->optionMap;
    }

    /**
     * Get option array for given billing frequency interval
     *
     * @param int $from
     * @param int|null $to
     * @return array
     */
    private function getOptionArray($from, $to = null)
    {
        $options = [];
        if (!$to) {
            $to = $from;
        }
        for ($every = $from; $every <= $to; $every++) {
            $options[] = [
                'value' => $every,
                'label' => $every . ' ' . $this->ending->getEnding($every)
            ];
        }
        return $options;
    }
}
