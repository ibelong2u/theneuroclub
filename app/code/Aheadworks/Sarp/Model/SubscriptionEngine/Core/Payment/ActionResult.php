<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\DataObject;

/**
 * Class ActionResult
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment
 */
class ActionResult extends DataObject
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ORDER = 'order';
    const STATUS = 'status';
    /**#@-*/

    /**
     * Get order
     *
     * @return OrderInterface|null
     */
    public function getOrder()
    {
        return $this->getData(self::ORDER);
    }

    /**
     * Get payment status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
}
