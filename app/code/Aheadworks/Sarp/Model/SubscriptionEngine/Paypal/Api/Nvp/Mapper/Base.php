<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Mapper;

use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp as ApiNvp;
use Aheadworks\Sarp\Model\SubscriptionEngine\Api\MapperInterface;
use Magento\Framework\DataObject\Mapper as DataObjectMapper;

/**
 * Class Base
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Mapper
 */
class Base implements MapperInterface
{
    /**
     * @var array
     */
    private $toApiMaps = [
        ApiNvp::SET_EXPRESS_CHECKOUT => [
            'amount' => 'PAYMENTREQUEST_0_AMT',
            'currency_code' => 'PAYMENTREQUEST_0_CURRENCYCODE',
            'return_url' => 'RETURNURL',
            'cancel_url' => 'CANCELURL',
            'solution_type' => 'SOLUTIONTYPE',
            'billing_type' => 'L_BILLINGTYPE0',
            'billing_agreement_description' => 'L_BILLINGAGREEMENTDESCRIPTION0',
            'suppress_shipping' => 'NOSHIPPING'
        ],
        ApiNvp::GET_EXPRESS_CHECKOUT_DETAILS => [
            'token' => 'TOKEN'
        ],
        ApiNvp::CREATE_RECURRING_PAYMENTS_PROFILE => [
            'token' => 'TOKEN',
            'start_date' => 'PROFILESTARTDATE',
            'profile_description' => 'DESC',
            'billing_period' => 'BILLINGPERIOD',
            'billing_frequency' => 'BILLINGFREQUENCY',
            'total_billing_cycles' => 'TOTALBILLINGCYCLES',
            'amount' => 'AMT',
            'trial_billing_period' => 'TRIALBILLINGPERIOD',
            'trial_billing_frequency' => 'TRIALBILLINGFREQUENCY',
            'trial_total_billing_cycles' => 'TRIALTOTALBILLINGCYCLES',
            'trial_amount' => 'TRIALAMT',
            'trial_tax_amount' => 'TRIALTAXAMT',
            'trial_shipping_amount' => 'TRIALSHIPPINGAMT',
            'currency_code' => 'CURRENCYCODE',
            'shipping_amount' => 'SHIPPINGAMT',
            'tax_amount' => 'TAXAMT',
            'initial_amount' => 'INITAMT'
        ],
        ApiNvp::UPDATE_RECURRING_PAYMENTS_PROFILE => [
            'profile_id' => 'PROFILEID',
            'start_date' => 'PROFILESTARTDATE',
            'profile_description' => 'DESC',
            'billing_period' => 'BILLINGPERIOD',
            'billing_frequency' => 'BILLINGFREQUENCY',
            'total_billing_cycles' => 'TOTALBILLINGCYCLES',
            'amount' => 'AMT',
            'trial_billing_period' => 'TRIALBILLINGPERIOD',
            'trial_billing_frequency' => 'TRIALBILLINGFREQUENCY',
            'trial_total_billing_cycles' => 'TRIALTOTALBILLINGCYCLES',
            'trial_amount' => 'TRIALAMT',
            'currency_code' => 'CURRENCYCODE',
            'shipping_amount' => 'SHIPPINGAMT',
            'tax_amount' => 'TAXAMT',
            'initial_amount' => 'INITAMT'
        ],
        ApiNvp::GET_RECURRING_PAYMENTS_PROFILE_DETAILS => [
            'profile_id' => 'PROFILEID'
        ],
        ApiNvp::MANAGE_RECURRING_PAYMENTS_PROFILE_STATUS => [
            'profile_id' => 'PROFILEID',
            'action' => 'ACTION',
            'note' => 'NOTE'
        ]
    ];

    /**
     * @var array
     */
    private $toApiDefaults = [
        ApiNvp::SET_EXPRESS_CHECKOUT => ['PAYMENTREQUEST_0_AMT' => 0]
    ];

    /**
     * @var array
     */
    private $fromApiMaps = [
        ApiNvp::SET_EXPRESS_CHECKOUT => ['TOKEN' => 'token'],
        ApiNvp::CREATE_RECURRING_PAYMENTS_PROFILE => [
            'PROFILEID' => 'profile_id',
            'PROFILESTATUS' => 'profile_status'
        ],
        ApiNvp::UPDATE_RECURRING_PAYMENTS_PROFILE => [
            'PROFILEID' => 'profile_id'
        ],
        ApiNvp::GET_RECURRING_PAYMENTS_PROFILE_DETAILS => [
            'PROFILEID' => 'profile_id',
            'STATUS' => 'status',
            'REGULARAMT' => 'amount',
            'REGULARSHIPPINGAMT' => 'shipping_amount',
            'REGULARTAXAMT' => 'tax_amount'
        ],
        ApiNvp::MANAGE_RECURRING_PAYMENTS_PROFILE_STATUS => [
            'PROFILEID' => 'profile_id'
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function toApi($method, $data)
    {
        return DataObjectMapper::accumulateByMap(
            $data,
            ['METHOD' => $method],
            isset($this->toApiMaps[$method]) ? $this->toApiMaps[$method] : [],
            isset($this->toApiDefaults[$method]) ? $this->toApiDefaults[$method] : []
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fromApi($method, $data)
    {
        return DataObjectMapper::accumulateByMap(
            $data,
            [],
            isset($this->fromApiMaps[$method]) ? $this->fromApiMaps[$method] : []
        );
    }
}
