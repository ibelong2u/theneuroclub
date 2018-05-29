<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionPlanExtensionInterface;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionPlan as SubscriptionPlanResource;
use Aheadworks\Sarp\Model\SubscriptionPlan\Validator as SubscriptionPlanValidator;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class SubscriptionPlan
 * @package Aheadworks\Sarp\Model
 */
class SubscriptionPlan extends AbstractModel implements SubscriptionPlanInterface
{
    /**
     * @var SubscriptionPlanValidator
     */
    private $validator;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param SubscriptionPlanValidator $validator
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SubscriptionPlanValidator $validator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(SubscriptionPlanResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptionPlanId()
    {
        return $this->getData(self::SUBSCRIPTION_PLAN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubscriptionPlanId($subscriptionPlanId)
    {
        return $this->setData(self::SUBSCRIPTION_PLAN_ID, $subscriptionPlanId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescriptions()
    {
        return $this->getData(self::DESCRIPTIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescriptions($descriptions)
    {
        return $this->setData(self::DESCRIPTIONS, $descriptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getStorefrontTitle()
    {
        return $this->getData(self::STOREFRONT_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setStorefrontTitle($storefrontTitle)
    {
        return $this->setData(self::STOREFRONT_TITLE, $storefrontTitle);
    }

    /**
     * {@inheritdoc}
     */
    public function getStorefrontDescription()
    {
        return $this->getData(self::STOREFRONT_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setStorefrontDescription($storefrontDescription)
    {
        return $this->setData(self::STOREFRONT_DESCRIPTION, $storefrontDescription);
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingPeriod()
    {
        return $this->getData(self::BILLING_PERIOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingPeriod($billingPeriod)
    {
        return $this->setData(self::BILLING_PERIOD, $billingPeriod);
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingFrequency()
    {
        return $this->getData(self::BILLING_FREQUENCY);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingFrequency($billingFrequency)
    {
        return $this->setData(self::BILLING_FREQUENCY, $billingFrequency);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalBillingCycles()
    {
        return $this->getData(self::TOTAL_BILLING_CYCLES);
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalBillingCycles($totalBillingCycles)
    {
        return $this->setData(self::TOTAL_BILLING_CYCLES, $totalBillingCycles);
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDateType()
    {
        return $this->getData(self::START_DATE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setStartDateType($startDateType)
    {
        return $this->setData(self::START_DATE_TYPE, $startDateType);
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDateDayOfMonth()
    {
        return $this->getData(self::START_DATE_DAY_OF_MONTH);
    }

    /**
     * {@inheritdoc}
     */
    public function setStartDateDayOfMonth($startDateDayOfMonth)
    {
        return $this->setData(self::START_DATE_DAY_OF_MONTH, $startDateDayOfMonth);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsInitialFeeEnabled()
    {
        return $this->getData(self::IS_INITIAL_FEE_ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsInitialFeeEnabled($isInitialFeeEnabled)
    {
        return $this->setData(self::IS_INITIAL_FEE_ENABLED, $isInitialFeeEnabled);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsTrialPeriodEnabled()
    {
        return $this->getData(self::IS_TRIAL_PERIOD_ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsTrialPeriodEnabled($isTrialPeriodEnabled)
    {
        return $this->setData(self::IS_TRIAL_PERIOD_ENABLED, $isTrialPeriodEnabled);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialTotalBillingCycles()
    {
        return $this->getData(self::TRIAL_TOTAL_BILLING_CYCLES);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialTotalBillingCycles($trialTotalBillingCycles)
    {
        return $this->setData(self::TRIAL_TOTAL_BILLING_CYCLES, $trialTotalBillingCycles);
    }

    /**
     * {@inheritdoc}
     */
    public function getEngineCode()
    {
        return $this->getData(self::ENGINE_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setEngineCode($engineCode)
    {
        return $this->setData(self::ENGINE_CODE, $engineCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(SubscriptionPlanExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }
}
