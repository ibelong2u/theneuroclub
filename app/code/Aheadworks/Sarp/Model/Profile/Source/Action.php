<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Profile\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Action
 * @package Aheadworks\Sarp\Model\Profile\Source
 */
class Action implements ArrayInterface
{
    /**
     * 'Update' action
     */
    const UPDATE = 'update';

    /**
     * 'Suspend' action
     */
    const SUSPEND = 'suspend';

    /**
     * 'Cancel' action
     */
    const CANCEL = 'cancel';

    /**
     * 'Activate' action
     */
    const ACTIVATE = 'activate';

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
                    'value' => self::UPDATE,
                    'label' => __('Update')
                ],
                [
                    'value' => self::SUSPEND,
                    'label' => __('Suspend')
                ],
                [
                    'value' => self::CANCEL,
                    'label' => __('Cancel')
                ],
                [
                    'value' => self::ACTIVATE,
                    'label' => __('Activate')
                ]
            ];
        }
        return $this->options;
    }
}
