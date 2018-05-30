<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api;

/**
 * Payment method management interface
 */
interface PaymentMethodManagementInterface
{
    /**
     * Get available payment methods for specified cart ID
     *
     * @param int $cartId
     * @return \Aheadworks\Sarp\Api\Data\PaymentMethodInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList($cartId);
}
