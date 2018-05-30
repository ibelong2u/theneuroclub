<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Profile\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * todo: consider automatic options generation using sources from all engines
 * Class Status
 * @package Aheadworks\Sarp\Model\Profile\Source
 */
class Status implements ArrayInterface
{
    /**
     * 'Active' status
     */
    const ACTIVE = 'active';

    /**
     * 'Pending' status
     */
    const PENDING = 'pending';

    /**
     * 'Cancelled' status
     */
    const CANCELLED = 'cancelled';

    /**
     * 'Suspended' status
     */
    const SUSPENDED = 'suspended';

    /**
     * 'Expired' status
     */
    const EXPIRED = 'expired';

    /**
     * 'Terminated' status
     */
    const TERMINATED = 'terminated';

    /**
     * 'Trialing' status
     */
    const TRAILING = 'trialing';

    /**
     * 'Past due' status
     */
    const PAST_DUE = 'past_due';

    /**
     * 'Unpaid' status
     */
    const UNPAID = 'unpaid';

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
                    'value' => self::ACTIVE,
                    'label' => __('Active')
                ],
                [
                    'value' => self::PENDING,
                    'label' => __('Pending')
                ],
                [
                    'value' => self::CANCELLED,
                    'label' => __('Cancelled')
                ],
                [
                    'value' => self::SUSPENDED,
                    'label' => __('Suspended')
                ],
                [
                    'value' => self::EXPIRED,
                    'label' => __('Expired')
                ],
                [
                    'value' => self::TERMINATED,
                    'label' => __('Terminated')
                ],
                [
                    'value' => self::PAST_DUE,
                    'label' => __('Trialing')
                ],
                [
                    'value' => self::TRAILING,
                    'label' => __('Past due')
                ],
                [
                    'value' => self::UNPAID,
                    'label' => __('Unpaid')
                ]
            ];
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
