<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Mapper;

use Aheadworks\Sarp\Model\SubscriptionEngine\Api\MapperInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class Base
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Mapper
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
        Api::CREATE_SUBSCRIPTION_REQUEST => [
            'profile_description' => 'subscription/name',
            'billing_frequency' => 'subscription/paymentSchedule/interval/length',
            'billing_period' => 'subscription/paymentSchedule/interval/unit',
            'start_date' => 'subscription/paymentSchedule/startDate',
            'total_billing_cycles' => 'subscription/paymentSchedule/totalOccurrences',
            'amount' => 'subscription/amount',
            'trial_amount' => 'subscription/trialAmount',
            'cc_number' => 'subscription/payment/creditCard/cardNumber',
            'cc_exp_date' => 'subscription/payment/creditCard/expirationDate',
            'trial_total_billing_cycles' => 'subscription/paymentSchedule/trialOccurrences'
        ],
        Api::GET_SUBSCRIPTION_REQUEST => [
            'profile_id' => 'subscriptionId'
        ],
        Api::GET_SUBSCRIPTION_STATUS_REQUEST => [
            'profile_id' => 'subscriptionId'
        ],
        Api::UPDATE_SUBSCRIPTION_REQUEST => [
            'profile_id' => 'subscriptionId',
            'profile_description' => 'subscription/name',
            'billing_frequency' => 'subscription/paymentSchedule/interval/length',
            'billing_period' => 'subscription/paymentSchedule/interval/unit',
            'start_date' => 'subscription/paymentSchedule/startDate',
            'total_billing_cycles' => 'subscription/paymentSchedule/totalOccurrences',
            'amount' => 'subscription/amount',
            'trial_amount' => 'subscription/trialAmount',
            'trial_total_billing_cycles' => 'subscription/paymentSchedule/trialOccurrences'
        ],
        Api::CANCEL_SUBSCRIPTION_REQUEST => [
            'profile_id' => 'subscriptionId'
        ]
    ];

    /**
     * @var array
     */
    private $toApiDefaults = [
        Api::CREATE_SUBSCRIPTION_REQUEST => [
            'total_billing_cycles' => 0
        ]
    ];

    /**
     * @var array
     */
    private $fromApiMaps = [
        Api::CREATE_SUBSCRIPTION_REQUEST => [
            'subscriptionId' => 'profile_id'
        ],
        Api::GET_SUBSCRIPTION_REQUEST => [
            'subscription/status' => 'status'
        ],
        Api::GET_SUBSCRIPTION_STATUS_REQUEST => [
            'status' => 'profile_status'
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function toApi($method, $data)
    {
        $result = [];
        $map = $this->getMap($this->toApiMaps, $method);
        if ($map) {
            $defaults = $this->getMap($this->toApiDefaults, $method, []);
            foreach ($map as $field => $path) {
                $value = $this->getToFieldValue($field, $data, $defaults);
                if ($value !== null) {
                    $result = $this->arrayManager->set($path, $result, $value);
                }
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function fromApi($method, $data)
    {
        $result = [];
        $map = $this->getMap($this->fromApiMaps, $method);
        if ($map) {
            foreach ($map as $path => $field) {
                $value = $this->arrayManager->get($path, $data);
                if ($value) {
                    $result[$field] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Get map for method
     *
     * @param array $maps
     * @param string $method
     * @param array|null $default
     * @return array|null
     */
    private function getMap($maps, $method, $default = null)
    {
        return isset($maps[$method])
            ? $maps[$method]
            : $default;
    }

    /**
     * Get to api field value
     *
     * @param string $fieldName
     * @param array $data
     * @param array $defaultData
     * @return mixed|null
     */
    private function getToFieldValue($fieldName, $data, $defaultData)
    {
        if (isset($data[$fieldName])) {
            return $data[$fieldName];
        }
        if (isset($defaultData[$fieldName])) {
            return $defaultData[$fieldName];
        }
        return null;
    }
}
