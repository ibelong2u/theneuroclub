<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Profile\Item;

use Aheadworks\Sarp\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp\Api\Data\ProfileItemInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\Sarp\Model\Profile\Item
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
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ProfileItemInterfaceFactory
     */
    private $itemsFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param ProfileItemInterfaceFactory $itemsFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        ProfileItemInterfaceFactory $itemsFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->entityManager = $entityManager;
        $this->itemsFactory = $itemsFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getProfileId();
        if ($entityId) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(ProfileItemInterface::class)->getEntityConnectionName()
            );

            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_sarp_profile_item'), 'item_id')
                ->where('profile_id = :id');
            $profileItemIds = $connection->fetchCol($select, ['id' => $entityId]);

            $items = [];
            $innerItems = [];
            foreach ($profileItemIds as $itemId) {
                /** @var ProfileItemInterface $item */
                $item = $this->itemsFactory->create();
                $this->entityManager->load($item, $itemId);
                $innerItems[] = $item;
                if (!$item->getParentItemId()) {
                    $items[] = $item;
                }
            }
            $entity
                ->setInnerItems($innerItems)
                ->setItems($items);
        }
        return $entity;
    }
}
