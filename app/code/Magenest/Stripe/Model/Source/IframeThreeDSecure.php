<?php

namespace Magenest\Stripe\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class IframeThreeDSecure implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('Affected by Settings of Stripe Payment')
            ],
        ];
    }
}
