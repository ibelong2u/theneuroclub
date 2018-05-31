<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Subscription plan interface
 * @api
 */
interface SubscriptionPlanInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const SUBSCRIPTION_PLAN_ID = 'subscription_plan_id';
    const STATUS = 'status';
    const NAME = 'name';
    const WEBSITE_ID = 'website_id';
    const DESCRIPTIONS = 'descriptions';
    const STOREFRONT_TITLE = 'storefront_title';
    const STOREFRONT_DESCRIPTION = 'storefront_description';
    const BILLING_PERIOD = 'billing_period';
    const BILLING_FREQUENCY = 'billing_frequency';
    const TOTAL_BILLING_CYCLES = 'total_billing_cycles';
    const START_DATE_TYPE = 'start_date_type';
    const START_DATE_DAY_OF_MONTH = 'start_date_day_of_month';
    const IS_INITIAL_FEE_ENABLED = 'is_initial_fee_enabled';
    const IS_TRIAL_PERIOD_ENABLED = 'is_trial_period_enabled';
    const TRIAL_TOTAL_BILLING_CYCLES = 'trial_total_billing_cycles';
    const ENGINE_CODE = 'engine_code';
    /**#@-*/

    /**
     * Get subscription plan ID
     *
     * @return int|null
     */
    public function getSubscriptionPlanId();

    /**
     * Set subscription plan ID
     *
     * @param int $subscriptionPlanId
     * @return $this
     */
    public function setSubscriptionPlanId($subscriptionPlanId);

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get website ID
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Set website ID
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * Get descriptions on storefront per store view
     *
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionPlanDescriptionInterface[]
     */
    public function getDescriptions();

    /**
     * Set descriptions on storefront per store view
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionPlanDescriptionInterface[] $descriptions
     * @return $this
     */
    public function setDescriptions($descriptions);

    /**
     * Get title on storefront
     *
     * @return string
     */
    public function getStorefrontTitle();

    /**
     * Set title on storefront
     *
     * @param string $storefrontTitle
     * @return $this
     */
    public function setStorefrontTitle($storefrontTitle);

    /**
     * Get description on storefront
     *
     * @return string
     */
    public function getStorefrontDescription();

    /**
     * Set description on storefront
     *
     * @param string $storefrontDescription
     * @return $this
     */
    public function setStorefrontDescription($storefrontDescription);

    /**
     * Get billing period
     *
     * @return string
     */
    public function getBillingPeriod();

    /**
     * Set billing period
     *
     * @param string $billingPeriod
     * @return $this
     */
    public function setBillingPeriod($billingPeriod);

    /**
     * Get billing frequency
     *
     * @return int
     */
    public function getBillingFrequency();

    /**
     * Set billing frequency
     *
     * @param int $billingFrequency
     * @return $this
     */
    public function setBillingFrequency($billingFrequency);

    /**
     * Get total billing cycles
     *
     * @return int
     */
    public function getTotalBillingCycles();

    /**
     * Set total billing cycles
     *
     * @param int $totalBillingCycles
     * @return $this
     */
    public function setTotalBillingCycles($totalBillingCycles);

    /**
     * Get start date type
     *
     * @return string
     */
    public function getStartDateType();

    /**
     * Set start date type
     *
     * @param string $startDateType
     * @return $this
     */
    public function setStartDateType($startDateType);

    /**
     * Get day of month of start date
     *
     * @return int|null
     */
    public function getStartDateDayOfMonth();

    /**
     * Set day of month of start date
     *
     * @param int $startDateDayOfMonth
     * @return $this
     */
    public function setStartDateDayOfMonth($startDateDayOfMonth);

    /**
     * Get is initial fee enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsInitialFeeEnabled();

    /**
     * Set is initial fee enabled
     *
     * @param bool $isInitialFeeEnabled
     * @return $this
     */
    public function setIsInitialFeeEnabled($isInitialFeeEnabled);

    /**
     * Get is trial period enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsTrialPeriodEnabled();

    /**
     * Set is trial period enabled
     *
     * @param bool $isTrialPeriodEnabled
     * @return $this
     */
    public function setIsTrialPeriodEnabled($isTrialPeriodEnabled);

    /**
     * Get trial total billing cycles
     *
     * @return int
     */
    public function getTrialTotalBillingCycles();

    /**
     * Set trial total billing cycles
     *
     * @param int $trialTotalBillingCycles
     * @return $this
     */
    public function setTrialTotalBillingCycles($trialTotalBillingCycles);

    /**
     * Get engine code
     *
     * @return string
     */
    public function getEngineCode();

    /**
     * Set engine codes
     *
     * @param string $engineCode
     * @return $this
     */
    public function setEngineCode($engineCode);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionPlanExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionPlanExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp\Api\Data\SubscriptionPlanExtensionInterface $extensionAttributes
    );
}
