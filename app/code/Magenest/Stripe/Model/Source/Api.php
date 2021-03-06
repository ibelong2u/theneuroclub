<?php

namespace Magenest\Stripe\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Api implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'v3',
                'label' => __('Stripe.js v3 & Elements (Recommended Integration)')
            ],
            [
                'value' => 'v2',
                'label' => __('Stripe.js v2'),
            ],
            [
                'value' => 'direct',
                'label' => __('Directly to the API (Not Recommend)'),
            ],
        ];
    }
}
