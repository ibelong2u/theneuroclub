<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel\CoreEngine;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription as SubscriptionModel;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Payment
 * @package Aheadworks\Sarp\Model\ResourceModel\CoreEngine
 */
class Payment extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sarp_core_payment', 'payment_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->_resources->getConnectionByName(
            $this->metadataPool->getMetadata(ProfileInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * Get last payment date of subscription
     *
     * @param int $subscriptionId
     * @return string|null
     * @throws LocalizedException
     */
    public function getLastPaymentDate($subscriptionId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), 'scheduled_at')
            ->order('scheduled_at DESC')
            ->where('subscription_id = :subscriptionId')
            ->where('status = :status');
        $result = $connection->fetchOne(
            $select,
            [
                'subscriptionId' => $subscriptionId,
                'status' => \Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment::STATUS_PAID
            ]
        );
        return $result ? : null;
    }
}
