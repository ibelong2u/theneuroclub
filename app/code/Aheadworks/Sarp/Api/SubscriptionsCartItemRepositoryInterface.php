<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api;

/**
 * Subscriptions cart item CRUD interface
 * @api
 */
interface SubscriptionsCartItemRepositoryInterface
{
    /**
     * Save subscriptions cart item
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface $item
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface $item);

    /**
     * Get list of subscriptions cart items
     *
     * @param int $cartId
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartItemSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList($cartId);

    /**
     * Delete subscriptions cart item by ID
     *
     * @param int $cartId
     * @param int $itemId
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface
     */
    public function deleteById($cartId, $itemId);
}
