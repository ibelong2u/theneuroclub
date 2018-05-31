<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Item;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Item
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
     * @var SubscriptionsCartItemInterfaceFactory
     */
    private $itemsFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param SubscriptionsCartItemInterfaceFactory $itemsFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        SubscriptionsCartItemInterfaceFactory $itemsFactory
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
        $entityId = (int)$entity->getCartId();
        if ($entityId) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(SubscriptionsCartItemInterface::class)->getEntityConnectionName()
            );

            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_sarp_subscriptions_cart_item'), 'item_id')
                ->where('cart_id = :id');
            $cartItemIds = $connection->fetchCol($select, ['id' => $entityId]);

            $items = [];
            $innerItems = [];
            foreach ($cartItemIds as $itemId) {
                /** @var SubscriptionsCartItemInterface $item */
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
