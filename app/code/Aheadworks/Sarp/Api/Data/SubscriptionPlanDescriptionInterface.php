<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Subscription plan description interface
 * @api
 */
interface SubscriptionPlanDescriptionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const SUBSCRIPTION_PLAN_ID = 'subscription_plan_id';
    const STORE_ID = 'store_id';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    /**#@-*/

    /**
     * Get subscription plan ID
     *
     * @return int|null
     */
    public function getSubscriptionPlanId();

    /**
     * Set subscription plan ID
     *
     * @param int $subscriptionPlanId
     * @return $this
     */
    public function setSubscriptionPlanId($subscriptionPlanId);

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionPlanDescriptionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionPlanDescriptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp\Api\Data\SubscriptionPlanDescriptionExtensionInterface $extensionAttributes
    );
}
