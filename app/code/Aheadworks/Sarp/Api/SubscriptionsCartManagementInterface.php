<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api;

/**
 * Subscriptions cart management interface
 */
interface SubscriptionsCartManagementInterface
{
    /**
     * Add item to subscriptions cart
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface $cart
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface $cartItem
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface
     */
    public function add(
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface $cart,
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface $cartItem
    );

    /**
     * Select subscription plan
     *
     * @param int $cartId
     * @param int|null $planId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     */
    public function selectSubscriptionPlan($cartId, $planId = null);

    /**
     * Merge subscriptions cart 2 into subscriptions cart 1
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface $cart1
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface $cart2
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     */
    public function merge(
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface $cart1,
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface $cart2
    );

    /**
     * Save shipping information
     *
     * @param int $cartId
     * @param \Aheadworks\Sarp\Api\Data\ShippingInformationInterface $shippingInformation
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     */
    public function saveShippingInformation(
        $cartId,
        \Aheadworks\Sarp\Api\Data\ShippingInformationInterface $shippingInformation
    );

    /**
     * Save guest shipping information
     *
     * @param int $cartId
     * @param string $email
     * @param \Aheadworks\Sarp\Api\Data\ShippingInformationInterface $shippingInformation
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     */
    public function saveGuestShippingInformation(
        $cartId,
        $email,
        \Aheadworks\Sarp\Api\Data\ShippingInformationInterface $shippingInformation
    );

    /**
     * Save billing address
     *
     * @param int $cartId
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $billingAddress
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     */
    public function saveBillingAddress(
        $cartId,
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $billingAddress
    );

    /**
     * Save guest billing address
     *
     * @param int $cartId
     * @param string $email
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $billingAddress
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     */
    public function saveGuestBillingAddress(
        $cartId,
        $email,
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $billingAddress
    );

    /**
     * Save payment information
     *
     * @param int $cartId
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface $paymentInformation
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface|null $billingAddress
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     */
    public function savePaymentInformation(
        $cartId,
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface $paymentInformation,
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $billingAddress = null
    );

    /**
     * Save payment information and submit subscriptions cart
     *
     * @param int $cartId
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface $paymentInformation
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface|null $billingAddress
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     */
    public function savePaymentInformationAndSubmit(
        $cartId,
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface $paymentInformation,
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $billingAddress = null
    );

    /**
     * Save guest payment information
     *
     * @param int $cartId
     * @param string $email
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface $paymentInformation
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface|null $billingAddress
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     */
    public function saveGuestPaymentInformation(
        $cartId,
        $email,
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface $paymentInformation,
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $billingAddress = null
    );

    /**
     * Save guest payment information and submit subscriptions cart
     *
     * @param int $cartId
     * @param string $email
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface $paymentInformation
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface|null $billingAddress
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     */
    public function saveGuestPaymentInformationAndSubmit(
        $cartId,
        $email,
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface $paymentInformation,
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $billingAddress = null
    );

    /**
     * Submit subscriptions cart and create profile
     *
     * @param int $cartId
     * @param array $additionalParams
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface|null $paymentInformation
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     */
    public function submit(
        $cartId,
        $additionalParams = [],
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface $paymentInformation = null
    );
}
