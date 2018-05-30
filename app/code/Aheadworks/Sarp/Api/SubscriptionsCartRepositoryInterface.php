<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api;

/**
 * Subscriptions cart CRUD interface
 * @api
 */
interface SubscriptionsCartRepositoryInterface
{
    /**
     * Save subscriptions cart
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface $cart
     * @param bool $recollectTotals
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface $cart, $recollectTotals = true);

    /**
     * Retrieve subscriptions cart by ID
     *
     * @param int $cartId
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($cartId);

    /**
     * Retrieve active subscriptions cart by ID
     *
     * @param int $cartId
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getActive($cartId);

    /**
     * Retrieve subscriptions cart by customer ID
     *
     * @param int $customerId
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getForCustomer($customerId);

    /**
     * Retrieve active subscriptions cart by customer ID
     *
     * @param int $customerId
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getActiveForCustomer($customerId);

    /**
     * Delete subscriptions cart
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface $cart
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface $cart);
}
