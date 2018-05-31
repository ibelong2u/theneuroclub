<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionPlan\Source;

use Aheadworks\Sarp\Model\SubscriptionPlan\Source\DayOfMonth\Ending;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class BillingFrequency
 * @package Aheadworks\Sarp\Model\SubscriptionPlan\Source
 */
class BillingFrequency implements ArrayInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var Ending
     */
    private $ending;

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
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];
            for ($every = 1; $every <= 365; $every++) {
                $this->options[] = [
                    'value' => $every,
                    'label' => $every . ' ' . $this->ending->getEnding($every)
                ];
            }
        }
        return $this->options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->toOptionArray() as $optionItem) {
            $options[$optionItem['value']] = $optionItem['label'];
        }
        return $options;
    }
}
