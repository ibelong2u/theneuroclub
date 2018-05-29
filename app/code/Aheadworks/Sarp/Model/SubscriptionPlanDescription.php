<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanDescriptionInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionPlanDescriptionExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class SubscriptionPlanDescription
 * @package Aheadworks\Sarp\Model
 */
class SubscriptionPlanDescription extends AbstractExtensibleObject implements SubscriptionPlanDescriptionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSubscriptionPlanId()
    {
        return $this->_get(self::SUBSCRIPTION_PLAN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubscriptionPlanId($subscriptionPlanId)
    {
        return $this->setData(self::SUBSCRIPTION_PLAN_ID, $subscriptionPlanId);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(SubscriptionPlanDescriptionExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
