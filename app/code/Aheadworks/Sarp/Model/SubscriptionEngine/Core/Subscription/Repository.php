<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription;

use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Subscription as SubscriptionResource;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\SubscriptionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Repository
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription
 */
class Repository
{
    /**
     * @var Subscription[]
     */
    private $instances = [];

    /**
     * @var Subscription[]
     */
    private $instancesByProfileId = [];

    /**
     * @var SubscriptionFactory
     */
    private $factory;

    /**
     * @var SubscriptionResource
     */
    private $resource;

    /**
     * @param SubscriptionFactory $factory
     * @param SubscriptionResource $resource
     */
    public function __construct(
        SubscriptionFactory $factory,
        SubscriptionResource $resource
    ) {
        $this->factory = $factory;
        $this->resource = $resource;
    }

    /**
     * Retrieve core subscription
     *
     * @param int $subscriptionId
     * @return Subscription
     * @throws NoSuchEntityException
     */
    public function get($subscriptionId)
    {
        if (!isset($this->instances[$subscriptionId])) {
            /** @var Subscription $subscription */
            $subscription = $this->factory->create();
            $this->resource->load($subscription, $subscriptionId);
            if (!$subscription->getSubscriptionId()) {
                throw NoSuchEntityException::singleField('subscriptionId', $subscriptionId);
            }
            $this->instances[$subscriptionId] = $subscription;
            $this->instancesByProfileId[$subscription->getProfileId()] = $subscription;
        }
        return $this->instances[$subscriptionId];
    }

    /**
     * Retrieve core subscription by profile Id
     *
     * @param int $profileId
     * @return Subscription
     * @throws NoSuchEntityException
     */
    public function getByProfileId($profileId)
    {
        if (!isset($this->instancesByProfileId[$profileId])) {
            /** @var Subscription $subscription */
            $subscription = $this->factory->create();
            $this->resource->loadByProfileId($subscription, $profileId);
            if (!$subscription->getSubscriptionId()) {
                throw NoSuchEntityException::singleField('profileId', $profileId);
            }
            $this->instancesByProfileId[$profileId] = $subscription;
            $this->instances[$subscription->getSubscriptionId()] = $subscription;
        }
        return $this->instancesByProfileId[$profileId];
    }

    /**
     * Save core subscription instance
     *
     * @param Subscription $subscription
     * @return Subscription
     * @throws CouldNotSaveException
     */
    public function save(Subscription $subscription)
    {
        try {
            $this->resource->save($subscription);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$subscription->getSubscriptionId()]);
        unset($this->instancesByProfileId[$subscription->getProfileId()]);
        return $this->get($subscription->getSubscriptionId());
    }
}
