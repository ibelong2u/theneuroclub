<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment;

use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment as PaymentResource;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'payment_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Payment::class, PaymentResource::class);
    }
}
