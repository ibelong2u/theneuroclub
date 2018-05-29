<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Config\Source;

use Magento\Framework\Locale\ListsInterface as LocaleLists;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class MerchantTimezone
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Config\Source
 */
class MerchantTimezone implements ArrayInterface
{
    /**
     * @var array
     */
    private $ignoredTimezones = [
        'Antarctica/Troll',
        'Asia/Chita',
        'Asia/Srednekolymsk',
        'Pacific/Bougainville'
    ];

    /**
     * @var array
     */
    private $options;

    /**
     * @var LocaleLists
     */
    private $localeLists;

    /**
     * @param LocaleLists $localeLists
     */
    public function __construct(LocaleLists $localeLists)
    {
        $this->localeLists = $localeLists;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                ['value' => null, 'label' => __('--Please Select--')]
            ];
            foreach ($this->localeLists->getOptionTimezones() as $timezoneValue) {
                if (!in_array($timezoneValue['value'], $this->ignoredTimezones)) {
                    $this->options[] = $timezoneValue;
                }
            }
        }
        return $this->options;
    }
}
