<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionPlan;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionEngine\PlanValidatorFactory;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\StartDateType;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 * @package Aheadworks\Sarp\Model\SubscriptionPlan
 */
class Validator extends AbstractValidator
{
    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var PlanValidatorFactory
     */
    private $engineSpecificValidatorFactory;

    /**
     * @param EngineMetadataPool $engineMetadataPool
     * @param PlanValidatorFactory $engineSpecificValidatorFactory
     */
    public function __construct(
        EngineMetadataPool $engineMetadataPool,
        PlanValidatorFactory $engineSpecificValidatorFactory
    ) {
        $this->engineMetadataPool = $engineMetadataPool;
        $this->engineSpecificValidatorFactory = $engineSpecificValidatorFactory;
    }

    /**
     * Returns true if and only if subscription plan entity meets the validation requirements
     *
     * @param SubscriptionPlanInterface $subscriptionPlan
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function isValid($subscriptionPlan)
    {
        $this->_clearMessages();

        if (!\Zend_Validate::is($subscriptionPlan->getEngineCode(), 'NotEmpty')) {
            $this->_addMessages(['Subscription engine is required.']);
            return false;
        }
        $isEngineCodeExist = \Zend_Validate::is(
            $subscriptionPlan->getEngineCode(),
            'InArray',
            ['haystack' => $this->engineMetadataPool->getEnginesCodes()]
        );
        if (!$isEngineCodeExist) {
            $this->_addMessages(['Subscription engine code is incorrect or unavailable.']);
            return false;
        }

        if (!$this->isEngineSpecificDataValid($subscriptionPlan)) {
            return false;
        }

        if (!\Zend_Validate::is($subscriptionPlan->getWebsiteId(), 'NotEmpty')) {
            $this->_addMessages(['Select website.']);
        }
        if (!\Zend_Validate::is($subscriptionPlan->getName(), 'NotEmpty')) {
            $this->_addMessages(['Name is required.']);
        }
        if ($subscriptionPlan->getTotalBillingCycles()
            && !$this->isNumeric($subscriptionPlan->getTotalBillingCycles())
        ) {
            $this->_addMessages(['Number of payments is not a number.']);
        }
        if ($subscriptionPlan->getStartDateType() == StartDateType::EXACT_DAY_OF_MONTH) {
            if (!\Zend_Validate::is($subscriptionPlan->getStartDateDayOfMonth(), 'NotEmpty')) {
                $this->_addMessages(['Day of month is required.']);
            } elseif (!$this->isNumeric($subscriptionPlan->getStartDateDayOfMonth())) {
                $this->_addMessages(['Day of month is not a number.']);
            } elseif (!$this->isGreaterThanZero($subscriptionPlan->getStartDateDayOfMonth())) {
                $this->_addMessages(['Day of month must be greater than 0.']);
            }
        }
        if ($subscriptionPlan->getIsTrialPeriodEnabled()) {
            if (!\Zend_Validate::is($subscriptionPlan->getTrialTotalBillingCycles(), 'NotEmpty')) {
                $this->_addMessages(['Number of trial payments is required.']);
            } elseif (!$this->isNumeric($subscriptionPlan->getTrialTotalBillingCycles())) {
                $this->_addMessages(['Number of trial payments is not a number.']);
            } elseif (!$this->isGreaterThanZero($subscriptionPlan->getTrialTotalBillingCycles())) {
                $this->_addMessages(['Number of trial payments must be greater than 0.']);
            }
        }

        if (!$this->isDescriptionsDataValid($subscriptionPlan)) {
            return false;
        }

        return empty($this->getMessages());
    }

    /**
     * Returns true if and only if subscription plan engine specific data are correct
     *
     * @param SubscriptionPlanInterface $subscriptionPlan
     * @return bool
     */
    private function isEngineSpecificDataValid(SubscriptionPlanInterface $subscriptionPlan)
    {
        $engineMetadata = $this->engineMetadataPool->getMetadata($subscriptionPlan->getEngineCode());
        $engineSpecificValidator = $this->engineSpecificValidatorFactory->create(
            $engineMetadata->getPlanValidatorClassName()
        );
        if (!$engineSpecificValidator->isValid($subscriptionPlan)) {
            $this->_addMessages($engineSpecificValidator->getMessages());
            return false;
        }
        return true;
    }

    /**
     * Returns true if and only if subscription plan descriptions data are correct
     *
     * @param SubscriptionPlanInterface $subscriptionPlan
     * @return bool
     */
    private function isDescriptionsDataValid(SubscriptionPlanInterface $subscriptionPlan)
    {
        $isAllStoreViewsDataPresents = false;
        $descriptionStoreIds = [];
        if ($subscriptionPlan->getDescriptions()) {
            foreach ($subscriptionPlan->getDescriptions() as $description) {
                if (!in_array($description->getStoreId(), $descriptionStoreIds)) {
                    array_push($descriptionStoreIds, $description->getStoreId());
                } else {
                    $this->_addMessages(['Duplicated store view in storefront descriptions found.']);
                    return false;
                }
                if ($description->getStoreId() == 0) {
                    $isAllStoreViewsDataPresents = true;
                }

                if (!\Zend_Validate::is($description->getTitle(), 'NotEmpty')) {
                    $this->_addMessages(['Storefront title is required.']);
                    return false;
                }
                if ($description->getDescription()) {
                    if (!\Zend_Validate::is($description->getDescription(), 'StringLength', ['max' => 256])) {
                        $this->_addMessages(['Storefront description is more than 256 characters long.']);
                        return false;
                    }
                }
            }
        }
        if (!$isAllStoreViewsDataPresents) {
            $this->_addMessages(
                ['Default values of storefront descriptions (for All Store Views option) aren\'t set.']
            );
            return false;
        }
        return true;
    }

    /**
     * Check if value is numeric
     *
     * @param int $value
     * @return bool
     */
    private function isNumeric($value)
    {
        return \Zend_Validate::is($value, 'Regex', ['pattern' => '/^\s*-?\d*(\.\d*)?\s*$/']);
    }

    /**
     * Check if value greater than 0
     *
     * @param int $value
     * @return bool
     */
    private function isGreaterThanZero($value)
    {
        return \Zend_Validate::is($value, 'GreaterThan', ['min' => 0]);
    }
}
