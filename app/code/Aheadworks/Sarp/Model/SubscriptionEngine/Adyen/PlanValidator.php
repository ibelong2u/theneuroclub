<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Adyen;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsPool;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class PlanValidator
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Adyen
 */
class PlanValidator extends AbstractValidator
{
    /**
     * Engine code
     */
    const ENGINE_CODE = 'adyen';

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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isValid($plan)
    {
        $this->_clearMessages();

        $engineLabel = $this->engineMetadata->getLabel();
        $isStartDateTypeCorrect = \Zend_Validate::is(
            $plan->getStartDateType(),
            'InArray',
            ['haystack' => $this->engineRestrictions->getStartDateTypes()]
        );
        if (!$isStartDateTypeCorrect) {
            $this->_addMessages(
                [
                    sprintf(
                        'Start date type %s doesn\'t supported by %s subscription engine.',
                        $plan->getStartDateType(),
                        $engineLabel
                    )
                ]
            );
        }
        if (!$this->engineRestrictions->isInitialFeeSupported() && $plan->getIsInitialFeeEnabled()) {
            $this->_addMessages(
                [
                    sprintf(
                        'Initial fee doesn\'t supported by %s subscription engine.',
                        $engineLabel
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
}
