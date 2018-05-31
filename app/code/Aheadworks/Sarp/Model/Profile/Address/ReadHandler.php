<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Profile\Address;

use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Api\Data\ProfileAddressInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\Sarp\Model\Profile\Address
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
     * @var ProfileAddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param ProfileAddressInterfaceFactory $addressFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        ProfileAddressInterfaceFactory $addressFactory
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
        $entityId = (int)$entity->getProfileId();
        if ($entityId) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(ProfileAddressInterface::class)->getEntityConnectionName()
            );

            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_sarp_profile_address'), 'address_id')
                ->where('profile_id = :id');
            $addressIds = $connection->fetchCol($select, ['id' => $entityId]);

            $addresses = [];
            foreach ($addressIds as $addressId) {
                /** @var ProfileAddressInterface $address */
                $address = $this->addressFactory->create();
                $this->entityManager->load($address, $addressId);
                $addresses[] = $address;
            }
            $entity->setAddresses($addresses);
        }
        return $entity;
    }
}
