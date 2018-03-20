<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Quinoid\homepagebanner\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class Bannertypes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Image')], ['value' => 1, 'label' => __('Video')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Image'), 1 => __('Video')];
    }
}
