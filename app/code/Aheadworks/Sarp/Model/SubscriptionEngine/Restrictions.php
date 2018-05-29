<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine;

/**
 * Class Restrictions
 * @package Aheadworks\Sarp\Model\SubscriptionEngine
 */
class Restrictions extends \Magento\Framework\DataObject implements RestrictionsInterface
{
    const SUBSCRIPTION_STATUSES = 'subscription_statuses';
    const SUBSCRIPTION_ACTIONS = 'subscription_actions';
    const SUBSCRIPTION_ACTIONS_MAP = 'subscription_actions_map';
    const UNITS_OF_TIME = 'units_of_time';
    const START_DATE_TYPES = 'start_date_types';
    const CAN_BE_FINITE = 'can_be_finite';
    const IS_INITIAL_FEE_SUPPORTED = 'is_initial_fee_supported';
    const IS_TRIAL_PERIOD_SUPPORTED = 'is_trial_period_supported';

    /**
     * {@inheritdoc}
     */
    public function getSubscriptionStatuses()
    {
        return $this->getData(self::SUBSCRIPTION_STATUSES);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptionActions()
    {
        return $this->getData(self::SUBSCRIPTION_ACTIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptionActionsMap()
    {
        return $this->getData(self::SUBSCRIPTION_ACTIONS_MAP);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitsOfTime()
    {
        return $this->getData(self::UNITS_OF_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDateTypes()
    {
        return $this->getData(self::START_DATE_TYPES);
    }

    /**
     * {@inheritdoc}
     */
    public function canBeFinite()
    {
        return $this->getData(self::CAN_BE_FINITE);
    }

    /**
     * {@inheritdoc}
     */
    public function isInitialFeeSupported()
    {
        return $this->getData(self::IS_INITIAL_FEE_SUPPORTED);
    }

    /**
     * {@inheritdoc}
     */
    public function isTrialPeriodSupported()
    {
        return $this->getData(self::IS_TRIAL_PERIOD_SUPPORTED);
    }
}
