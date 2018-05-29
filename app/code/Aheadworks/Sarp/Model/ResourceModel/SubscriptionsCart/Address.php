<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Address
 * @package Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart
 */
class Address extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sarp_subscriptions_cart_address', 'address_id');
    }
}
