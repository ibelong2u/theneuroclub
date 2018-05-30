<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Level
 * @package Aheadworks\Sarp\Model\Logger\Source
 */
class Level implements OptionSourceInterface
{
    /**
     * 'Notice' status
     */
    const NOTICE = 'notice';

    /**
     * 'Warning' status
     */
    const WARNING = 'warning';

    /**
     * 'Error' status
     */
    const ERROR = 'error';

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
                    'value' => self::NOTICE,
                    'label' => __('Notice')
                ],
                [
                    'value' => self::WARNING,
                    'label' => __('Warning')
                ],
                [
                    'value' => self::ERROR,
                    'label' => __('Error')
                ]
            ];
        }
        return $this->options;
    }
}
