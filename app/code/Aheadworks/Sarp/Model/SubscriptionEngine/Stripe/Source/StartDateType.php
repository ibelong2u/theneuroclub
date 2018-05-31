<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Source;

use Aheadworks\Sarp\Model\SubscriptionPlan\Source\StartDateType as StartDateTypeSource;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class StartDateType
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Source
 */
class StartDateType implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'value' => StartDateTypeSource::MOMENT_OF_PURCHASE,
                    'label' => __('Moment of purchase')
                ]
            ];
        }
        return $this->options;
    }
}
