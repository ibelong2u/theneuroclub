<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Profile;

use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Repository;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class UpdateHandler
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Profile
 */
class UpdateHandler implements ExtensionInterface
{
    /**
     * @var Repository
     */
    private $subscriptionRepo;

    /**
     * @param Repository $subscriptionRepo
     */
    public function __construct(Repository $subscriptionRepo)
    {
        $this->subscriptionRepo = $subscriptionRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        if (isset($arguments['coreSubscriptionToUpdate'])) {
            /** @var Subscription $subscription */
            $subscription = $arguments['coreSubscriptionToUpdate'];
            if ($subscription->getProfileId() == $entity->getProfileId()) {
                $this->subscriptionRepo->save($subscription);
            }
        }
        return $entity;
    }
}
