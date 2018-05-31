<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api;

/**
 * Interface ShippingEstimationInterface
 * @package Aheadworks\Sarp\Api
 */
interface ShippingEstimationInterface
{
    /**
     * Estimate shipping
     *
     * @param int $cartId
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $shippingAddress
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function estimate(
        $cartId,
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $shippingAddress
    );

    /**
     * Estimate shipping by customer address Id
     *
     * @param int $cartId
     * @param int $customerAddressId
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function estimateByCustomerAddressId($cartId, $customerAddressId);
}
