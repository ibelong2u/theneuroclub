<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface SubscriptionsCartPaymentInterface
 * @package Aheadworks\Sarp\Api\Data
 */
interface SubscriptionsCartPaymentInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const PAYMENT_ID = 'payment_id';
    const CART_ID = 'cart_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const METHOD_CODE = 'method_code';
    const PAYMENT_DATA = 'payment_data';
    /**#@-*/

    /**
     * Get payment ID
     *
     * @return int|null
     */
    public function getPaymentId();

    /**
     * Set payment ID
     *
     * @param int $paymentId
     * @return $this
     */
    public function setPaymentId($paymentId);

    /**
     * Get cart ID
     *
     * @return int
     */
    public function getCartId();

    /**
     * Set cart ID
     *
     * @param int $cartId
     * @return $this
     */
    public function setCartId($cartId);

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set creation time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set update time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getMethodCode();

    /**
     * Set payment method code
     *
     * @param string $methodCode
     * @return $this
     */
    public function setMethodCode($methodCode);

    /**
     * Get payment data
     *
     * @return string[]|null
     */
    public function getPaymentData();

    /**
     * Set payment data
     *
     * @param string[] $paymentData
     * @return $this
     */
    public function setPaymentData($paymentData);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentExtensionInterface $extensionAttributes
    );
}
