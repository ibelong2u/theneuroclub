<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Subscription plan CRUD interface
 * @api
 */
interface SubscriptionPlanRepositoryInterface
{
    /**
     * Save subscription plan
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface $subscriptionPlan
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface $subscriptionPlan);

    /**
     * Retrieve subscription plan
     *
     * @param int $subscriptionPlanId
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($subscriptionPlanId);

    /**
     * Retrieve subscription plans matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionPlanSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete subscription plan
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface $subscriptionPlan
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface $subscriptionPlan);

    /**
     * Delete subscription plan by ID
     *
     * @param int $subscriptionPlanId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($subscriptionPlanId);
}
