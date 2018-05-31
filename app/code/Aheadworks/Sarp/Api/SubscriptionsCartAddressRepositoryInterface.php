<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api;

/**
 * Address CRUD interface
 * @api
 */
interface SubscriptionsCartAddressRepositoryInterface
{
    /**
     * Save address
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $address
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $address);

    /**
     * Retrieve address
     *
     * @param int $addressId
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($addressId);

    /**
     * Get list of cart addresses
     *
     * @param int $cartId
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList($cartId);
}
