<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionPlan\Description;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionPlanDescriptionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 * @package Aheadworks\Sarp\Model\SubscriptionPlan\Description
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
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(MetadataPool $metadataPool, ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getSubscriptionPlanId();
        $descriptions = $entity->getDescriptions() ? : [];

        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(SubscriptionPlanInterface::class)->getEntityConnectionName()
        );
        $tableName = $this->resourceConnection->getTableName('aw_sarp_subscription_plan_description');

        $connection->delete($tableName, ['subscription_plan_id = ?' => $entityId]);
        $toInsert = [];
        /** @var SubscriptionPlanDescriptionInterface $description */
        foreach ($descriptions as $description) {
            $toInsert[] = [
                'subscription_plan_id' => $entityId,
                'store_id' => $description->getStoreId(),
                'title' => $description->getTitle(),
                'description' => $description->getDescription()
            ];
        }
        if ($toInsert) {
            $connection->insertMultiple($tableName, $toInsert);
        }

        return $entity;
    }
}
