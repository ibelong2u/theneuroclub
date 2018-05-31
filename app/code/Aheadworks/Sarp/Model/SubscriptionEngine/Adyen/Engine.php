<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Adyen;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\SubscriptionEngine as CoreEngine;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineInterface;

/**
 * Class Engine
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Adyen
 */
class Engine implements EngineInterface
{
    /**
     * @var CoreEngine
     */
    private $coreEngine;

    /**
     * @param CoreEngine $coreEngine
     */
    public function __construct(CoreEngine $coreEngine)
    {
        $this->coreEngine = $coreEngine;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function submitProfile(
        ProfileInterface $profile,
        $additionalParams = [],
        SubscriptionsCartPaymentInterface $paymentInformation = null
    ) {
        return $this->coreEngine->createSubscription($profile, $paymentInformation);
    }

    /**
     * {@inheritdoc}
     */
    public function updateProfile(ProfileInterface $profile)
    {
        return $this->coreEngine->updateSubscription($profile);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileData($referenceId)
    {
        return $this->coreEngine->getSubscriptionData($referenceId);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function changeStatus($referenceId, $action, $additionalData = [])
    {
        return $this->coreEngine->changeStatus($referenceId, $action);
    }
}
