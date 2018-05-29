<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\BillingPeriod;
use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsPool;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class PlanValidator
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal
 */
class PlanValidator extends AbstractValidator
{
    const ENGINE_CODE = 'paypal';

    /**
     * @var EngineMetadataInterface
     */
    private $engineMetadata;

    /**
     * @var RestrictionsInterface
     */
    private $engineRestrictions;

    /**
     * @param EngineMetadataPool $engineMetadataPool
     * @param RestrictionsPool $engineRestrictionsPool
     */
    public function __construct(
        EngineMetadataPool $engineMetadataPool,
        RestrictionsPool $engineRestrictionsPool
    ) {
        $this->engineMetadata = $engineMetadataPool->getMetadata(self::ENGINE_CODE);
        $this->engineRestrictions = $engineRestrictionsPool->getRestrictions(self::ENGINE_CODE);
    }

    /**
     * Returns true if and only if subscription plan engine specific data are correct
     *
     * @param SubscriptionPlanInterface $plan
     * @return bool
     */
    public function isValid($plan)
    {
        $this->_clearMessages();

        if (!$this->isBillingPeriodDetailsValid($plan)) {
            return false;
        }

        if (!$this->engineRestrictions->isInitialFeeSupported() && $plan->getIsInitialFeeEnabled()) {
            $this->_addMessages(
                [
                    sprintf(
                        'Initial fee doesn\'t supported by %s subscription engine.',
                        $this->engineMetadata->getLabel()
                    )
                ]
            );
        }
        if (!$this->engineRestrictions->isTrialPeriodSupported() && $plan->getIsTrialPeriodEnabled()) {
            $this->_addMessages(
                [
                    sprintf(
                        'Trial period doesn\'t supported by %s subscription engine.',
                        $this->engineMetadata->getLabel()
                    )
                ]
            );
        }

        return empty($this->getMessages());
    }

    /**
     * Returns true if and only if billing period details of subscription plan data are correct
     *
     * @param SubscriptionPlanInterface $plan
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function isBillingPeriodDetailsValid(SubscriptionPlanInterface $plan)
    {
        $billingPeriod = $plan->getBillingPeriod();
        $billingFrequency = $plan->getBillingFrequency();

        $isBillingPeriodCorrect = \Zend_Validate::is(
            $billingPeriod,
            'InArray',
            ['haystack' => $this->engineRestrictions->getUnitsOfTime()]
        );
        if (!$isBillingPeriodCorrect) {
            $this->_addMessages(
                [
                    sprintf(
                        'Billing period doesn\'t supported by %s subscription engine.',
                        $this->engineMetadata->getLabel()
                    )
                ]
            );
            return false;
        }
        if (!\Zend_Validate::is($billingFrequency, 'GreaterThan', ['min' => 0])) {
            $this->_addMessages(['Billing frequency is invalid.']);
            return false;
        }
        if ($billingPeriod == BillingPeriod::DAY
            && \Zend_Validate::is($billingFrequency, 'GreaterThan', ['min' => 365])
            || $billingPeriod == BillingPeriod::WEEK
            && \Zend_Validate::is($billingFrequency, 'GreaterThan', ['min' => 52])
            || $billingPeriod == BillingPeriod::MONTH
            && \Zend_Validate::is($billingFrequency, 'GreaterThan', ['min' => 12])
            || $billingPeriod == BillingPeriod::YEAR
            && \Zend_Validate::is($billingFrequency, 'GreaterThan', ['min' => 1])
        ) {
            $this->_addMessages(
                [
                    sprintf(
                        'The combination of billing period and billing frequency '
                        . 'cannot exceed one year for %s subscription engine.',
                        $this->engineMetadata->getLabel()
                    )
                ]
            );
            return false;
        }
        if ($billingPeriod == BillingPeriod::SEMI_MONTH
            && \Zend_Validate::is($billingFrequency, 'GreaterThan', ['min' => 1])
        ) {
            $this->_addMessages(
                [
                    sprintf(
                        'If the billing period is SemiMonth, '
                        . 'the billing frequency must be 1 for %s subscription engine.',
                        $this->engineMetadata->getLabel()
                    )
                ]
            );
            return false;
        }

        return true;
    }
}
