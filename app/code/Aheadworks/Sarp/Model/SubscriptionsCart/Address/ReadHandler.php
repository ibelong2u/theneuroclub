<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Address;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Address
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
     * @var SubscriptionsCartAddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param SubscriptionsCartAddressInterfaceFactory $addressFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        SubscriptionsCartAddressInterfaceFactory $addressFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->entityManager = $entityManager;
        $this->addressFactory = $addressFactory;
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
                $this->metadataPool->getMetadata(SubscriptionsCartAddressInterface::class)->getEntityConnectionName()
            );

            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_sarp_subscriptions_cart_address'), 'address_id')
                ->where('cart_id = :id');
            $addressIds = $connection->fetchCol($select, ['id' => $entityId]);

            $addresses = [];
            foreach ($addressIds as $addressId) {
                /** @var SubscriptionsCartAddressInterface $address */
                $address = $this->addressFactory->create();
                $this->entityManager->load($address, $addressId);
                $addresses[] = $address;
            }
            $entity->setAddresses($addresses);
        }
        return $entity;
    }
}
