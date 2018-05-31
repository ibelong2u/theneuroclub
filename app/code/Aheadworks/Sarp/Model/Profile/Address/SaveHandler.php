<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Profile\Address;

use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class SaveHandler
 * @package Aheadworks\Sarp\Model\Profile\Address
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $profileAddresses = $entity->getAddresses();
        /** @var ProfileAddressInterface $address */
        foreach ($profileAddresses as $address) {
            try {
                $address->setProfileId($entity->getProfileId());
                $this->entityManager->save($address);
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('Could not save recurring profile.'));
            }
        }
        return $entity;
    }
}
