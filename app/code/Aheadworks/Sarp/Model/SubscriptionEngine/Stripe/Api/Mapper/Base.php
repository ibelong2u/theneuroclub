<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Mapper;

use Aheadworks\Sarp\Model\SubscriptionEngine\Api\MapperInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class Base
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Mapper
 */
class Base implements MapperInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(ArrayManager $arrayManager)
    {
        $this->arrayManager = $arrayManager;
    }

    /**
     * @var array
     */
    private $toApiMaps = [
        Api::PATH_PLANS => [
            'id' => 'id',
            'name' => 'name',
            'billing_frequency' => 'interval_count',
            'billing_period' => 'interval',
            'amount' => 'amount',
            'currency' => 'currency'
        ],
        Api::PATH_CUSTOMERS => [
            'customer_email' => 'email',
            'token' => 'source',
            'shipping_address/customer_name' => 'shipping/name',
            'shipping_address/telephone' => 'shipping/phone',
            'shipping_address/street/0' => 'shipping/address/line1',
            'shipping_address/street/1' => 'shipping/address/line2',
            'shipping_address/city' => 'shipping/address/city',
            'shipping_address/country_id' => 'shipping/address/country',
            'shipping_address/postcode' => 'shipping/address/postal_code',
            'shipping_address/state' => 'shipping/address/state'
        ],
        Api::PATH_SUBSCRIPTIONS => [
            'id' => 'id',
            'plan' => 'plan',
            'customer' => 'customer'
        ],
        Api::PATH_EVENTS => [
            'id' => 'id'
        ]
    ];

    /**
     * @var array
     */
    private $fromApiMaps = [
        Api::PATH_PLANS => [
            'id' => 'id'
        ],
        Api::PATH_CUSTOMERS => [
            'id' => 'id'
        ],
        Api::PATH_SUBSCRIPTIONS => [
            'id' => 'profile_id',
            'status' => 'profile_status',
            'customer' => 'customer_id',
            'plan/id' => 'plan_id'
        ],
        Api::PATH_EVENTS => [
            'id' => 'id',
            'type' => 'type',
            'data/object' => 'event_object_data'
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function toApi($resource, $data)
    {
        if (isset($this->toApiMaps[$resource])) {
            return $this->map($this->toApiMaps[$resource], $data);
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function fromApi($resource, $data)
    {
        if (isset($this->fromApiMaps[$resource])) {
            return $this->map($this->fromApiMaps[$resource], $data);
        }
        return [];
    }

    /**
     * Perform mapping
     *
     * @param array $map
     * @param array $data
     * @return array
     */
    private function map($map, $data)
    {
        $result = [];
        foreach ($map as $fromPath => $toPath) {
            $value = $this->arrayManager->get($fromPath, $data);
            if ($value !== null) {
                $result = $this->arrayManager->set($toPath, $result, $value);
            }
        }
        return $result;
    }
}
