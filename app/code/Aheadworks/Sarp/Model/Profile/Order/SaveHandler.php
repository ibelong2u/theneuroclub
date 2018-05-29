<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Profile\Order;

use Aheadworks\Sarp\Api\Data\ProfileOrderInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class SaveHandler
 * @package Aheadworks\Sarp\Model\Profile\Order
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        if (isset($arguments['order_id']) && $arguments['order_id']) {
            $connection = $this->getConnection();
            $tableName = $this->resourceConnection->getTableName('aw_sarp_profile_order');
            try {
                $data = [
                    'profile_id' => (int)$entity->getProfileId(),
                    'order_id' => (int)$arguments['order_id'],
                ];
                $connection->insert($tableName, $data);
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('Could not save recurring profile.'));
            }
        }
        return $entity;
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        $connection = null;
        try {
            $connectionName = $this->metadataPool->getMetadata(ProfileOrderInterface::class)->getEntityConnectionName();
            $connection = $this->resourceConnection->getConnectionByName($connectionName);
        } catch (\Exception $e) {
            $connection = $this->resourceConnection->getConnectionByName(ResourceConnection::DEFAULT_CONNECTION);
        }
        return $connection;
    }
}
