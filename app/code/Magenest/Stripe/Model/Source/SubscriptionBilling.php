<?php

namespace Magenest\Stripe\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class SubscriptionBilling implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'charge_automatically',
                'label' => __('Charge Automatically'),
            ],
            [
                'value' => 'send_invoice',
                'label' => __('Send Invoice')
            ]
        ];
    }
}
