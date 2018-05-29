<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription;

use Aheadworks\Sarp\Model\Profile\Source\Status;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineAvailability;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;

/**
 * Class PayableChecker
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription
 */
class PayableChecker
{
    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var EngineAvailability
     */
    private $engineAvailability;

    /**
     * @param EngineMetadataPool $engineMetadataPool
     * @param EngineAvailability $engineAvailability
     */
    public function __construct(
        EngineMetadataPool $engineMetadataPool,
        EngineAvailability $engineAvailability
    ) {
        $this->engineMetadataPool = $engineMetadataPool;
        $this->engineAvailability = $engineAvailability;
    }

    /**
     * Check if payments are allowed for subscription
     *
     * @param Subscription $subscription
     * @param bool $isReattempt
     * @return bool
     */
    public function isPayable(Subscription $subscription, $isReattempt = false)
    {
        $metadata = $this->engineMetadataPool->getMetadata($subscription->getEngineCode());
        $isAvailable = $this->engineAvailability->isAvailable($metadata);
        if ($isAvailable) {
            if ($isReattempt && $subscription->getPaymentFailuresCount() == 0) {
                return false;
            } else {
                $disallowedStatuses = $isReattempt
                    ? [Status::CANCELLED, Status::EXPIRED]
                    : [Status::CANCELLED, Status::EXPIRED, Status::SUSPENDED];
                return !in_array($subscription->getStatus(), $disallowedStatuses);
            }
        }
        return false;
    }
}
