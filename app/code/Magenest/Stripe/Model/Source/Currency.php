<?php

namespace Magenest\Stripe\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Currency implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'base',
                'label' => __('Base Currency')
            ],
            [
                'value' => 'store',
                'label' => __('Store Currency'),
            ],
        ];
    }
}
