<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for subscriptions cart items search results
 * @api
 */
interface SubscriptionsCartItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items list
     *
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface[]
     */
    public function getItems();

    /**
     * Set items list
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
