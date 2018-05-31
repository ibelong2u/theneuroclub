<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class Payment
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 */
class Payment extends AbstractExtensibleObject implements SubscriptionsCartPaymentInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPaymentId()
    {
        return $this->_get(self::PAYMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentId($paymentId)
    {
        return $this->setData(self::PAYMENT_ID, $paymentId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCartId()
    {
        return $this->_get(self::CART_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCartId($cartId)
    {
        return $this->setData(self::CART_ID, $cartId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodCode()
    {
        return $this->_get(self::METHOD_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethodCode($methodCode)
    {
        return $this->setData(self::METHOD_CODE, $methodCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentData()
    {
        return $this->_get(self::PAYMENT_DATA);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentData($paymentData)
    {
        return $this->setData(self::PAYMENT_DATA, $paymentData);
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
    public function setExtensionAttributes(SubscriptionsCartPaymentExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
