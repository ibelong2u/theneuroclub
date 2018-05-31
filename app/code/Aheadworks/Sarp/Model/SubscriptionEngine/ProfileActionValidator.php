<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Magento\Framework\Phrase;

/**
 * Class ProfileActionValidator
 * @package Aheadworks\Sarp\Model\SubscriptionEngine
 */
class ProfileActionValidator
{
    /**
     * @var RestrictionsPool
     */
    private $restrictionsPool;

    /**
     * @var Phrase
     */
    private $message;

    /**
     * @param RestrictionsPool $restrictionsPool
     */
    public function __construct(RestrictionsPool $restrictionsPool)
    {
        $this->restrictionsPool = $restrictionsPool;
    }

    /**
     * Check if profile is valid for action
     *
     * @param ProfileInterface $profile
     * @param string $action
     * @return bool
     */
    public function isValidForAction($profile, $action)
    {
        $this->message = null;

        $engineCode = $profile->getEngineCode();
        $restrictions = $this->restrictionsPool->getRestrictions($engineCode);
        $isActionSupportedForEngine = \Zend_Validate::is(
            $action,
            'InArray',
            ['haystack' => $restrictions->getSubscriptionActions()]
        );
        if (!$isActionSupportedForEngine) {
            $this->message = __('Action %1 is not supported by %2 subscription engine.', $action, $engineCode);
            return false;
        }

        $status = $profile->getStatus();
        $actionsMap = $restrictions->getSubscriptionActionsMap();
        $isActionSupportedForProfileStatus = \Zend_Validate::is(
            $action,
            'InArray',
            ['haystack' => $actionsMap[$status]]
        );
        if (!$isActionSupportedForProfileStatus) {
            $this->message = __('Action %1 is not supported for %2 profile status.', $action, $status);
            return false;
        }

        return true;
    }

    /**
     * Get validation message
     *
     * @return Phrase
     */
    public function getMessage()
    {
        return $this->message;
    }
}
