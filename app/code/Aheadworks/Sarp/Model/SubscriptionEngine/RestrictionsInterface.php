<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine;

/**
 * Interface RestrictionsInterface
 * @package Aheadworks\Sarp\Model\SubscriptionEngine
 */
interface RestrictionsInterface
{
    /**
     * Get subscription statuses
     *
     * @return array
     */
    public function getSubscriptionStatuses();

    /**
     * Get subscription actions
     *
     * @return array
     */
    public function getSubscriptionActions();

    /**
     * Get subscription actions map
     *
     * @return array
     */
    public function getSubscriptionActionsMap();

    /**
     * Get units of time
     *
     * @return array
     */
    public function getUnitsOfTime();

    /**
     * Get start date types
     *
     * @return array
     */
    public function getStartDateTypes();

    /**
     * Check if subscription can be finite
     *
     * @return bool
     */
    public function canBeFinite();

    /**
     * Check if initial fee supported
     *
     * @return bool
     */
    public function isInitialFeeSupported();

    /**
     * Check if trial period supported
     *
     * @return bool
     */
    public function isTrialPeriodSupported();
}
