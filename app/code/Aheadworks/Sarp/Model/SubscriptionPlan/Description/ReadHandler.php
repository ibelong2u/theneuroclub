<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionPlan\Description;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionPlanDescriptionInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionPlanDescriptionInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\Sarp\Model\SubscriptionPlan\Description
 */
class ReadHandler implements ExtensionInterface
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
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var SubscriptionPlanDescriptionInterfaceFactory
     */
    private $subscriptionPlanDescriptionFactory;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param DataObjectHelper $dataObjectHelper
     * @param SubscriptionPlanDescriptionInterfaceFactory $subscriptionPlanDescriptionFactory
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectHelper $dataObjectHelper,
        SubscriptionPlanDescriptionInterfaceFactory $subscriptionPlanDescriptionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->subscriptionPlanDescriptionFactory = $subscriptionPlanDescriptionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getSubscriptionPlanId();
        if ($entityId) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(SubscriptionPlanInterface::class)->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_sarp_subscription_plan_description'))
                ->where('subscription_plan_id = :id');
            $descriptionsData = $connection->fetchAll($select, ['id' => $entityId]);

            $descriptions = [];
            $storeFrontTitle = null;
            $storeFrontDescription = null;
            foreach ($descriptionsData as $data) {
                $descriptionEntity = $this->subscriptionPlanDescriptionFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $descriptionEntity,
                    $data,
                    SubscriptionPlanDescriptionInterface::class
                );
                $descriptions[] = $descriptionEntity;

                if (isset($arguments['store_id']) && $data['store_id'] == $arguments['store_id']) {
                    list($storeFrontTitle, $storeFrontDescription) = [$data['title'], $data['description']];
                }
                if ($data['store_id'] == 0) {
                    if (!isset($arguments['store_id'])) {
                        list($storeFrontTitle, $storeFrontDescription) = [$data['title'], $data['description']];
                    }
                    if (!$storeFrontTitle) {
                        $storeFrontTitle = $data['title'];
                    }
                    if (!$storeFrontDescription) {
                        $storeFrontDescription = $data['description'];
                    }
                }
            }
            $entity
                ->setDescriptions($descriptions)
                ->setStorefrontTitle($storeFrontTitle)
                ->setStorefrontDescription($storeFrontDescription);
        }
        return $entity;
    }
}
