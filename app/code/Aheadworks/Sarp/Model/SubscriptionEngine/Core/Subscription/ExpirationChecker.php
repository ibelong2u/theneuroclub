<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription;

/**
 * Class ExpirationChecker
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription
 */
class ExpirationChecker
{
    /**
     * Check if subscription expire
     *
     * @param Subscription $subscription
     * @param ProfileInterface $profile
     * @return bool
     */
    public function isExpire(Subscription $subscription, ProfileInterface $profile)
    {
        $totalBillingCycles =
            (($profile->getIsTrialPeriodEnabled()) ? $profile->getTrialTotalBillingCycles() : 0)
            + $profile->getTotalBillingCycles();
        $billingCycles = $subscription->getTrialPaymentsCount() + $subscription->getRegularPaymentsCount();
        return $profile->getTotalBillingCycles() > 0 && $billingCycles >= $totalBillingCycles;
    }
}
