<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class SubscriptionsCart
 * @package Aheadworks\Sarp\Model\ResourceModel
 */
class SubscriptionsCart extends AbstractDb
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
        $this->_init('aw_sarp_subscriptions_cart', 'cart_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->_resources->getConnectionByName(
            $this->metadataPool->getMetadata(SubscriptionsCartInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * Get cart ID by customer ID
     *
     * @param int $customerId
     * @return bool|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCartIdByCustomerId($customerId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), 'cart_id')
            ->where('customer_id = :customerId')
            ->where('is_active = ?', 1)
            ->order('updated_at ' . Select::SQL_DESC);
        $cartId = $connection->fetchOne($select, ['customerId' => $customerId]);
        return $cartId ? (int)$cartId : false;
    }
}
