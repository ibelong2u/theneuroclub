<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for subscription plan search results
 * @api
 */
interface SubscriptionPlanSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get subscription plans list
     *
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface[]
     */
    public function getItems();

    /**
     * Set subscription plans list
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
