<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core;

use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment as PaymentResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Payment
 *
 * @method int getPaymentId()
 * @method Payment setPaymentId(int $paymentId)
 * @method int getSubscriptionId()
 * @method Payment setSubscriptionId(int $subscriptionId)
 * @method int getOrderId()
 * @method Payment setOrderId(int $orderId)
 * @method string getStatus()
 * @method Payment setStatus(string $status)
 * @method string getType()
 * @method Payment setType(string $type)
 * @method string getScheduledAt()
 * @method Payment setScheduledAt(string $scheduledAt)
 * @method string getRetryAt()
 * @method Payment setRetryAt(string $retryAt)
 * @method int getRetriesCount()
 * @method Payment setRetriesCount(int $retriesCount)
 *
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core
 */
class Payment extends AbstractModel
{
    /**
     * Payment statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_RETRYING = 'retrying';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(PaymentResource::class);
    }
}
