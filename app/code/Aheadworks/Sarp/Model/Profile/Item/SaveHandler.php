<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Profile\Item;

use Aheadworks\Sarp\Api\Data\ProfileItemInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class SaveHandler
 * @package Aheadworks\Sarp\Model\Profile\Item
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
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $profileItems = $entity->getInnerItems();
        /** @var ProfileItemInterface $item */
        foreach ($profileItems as $item) {
            try {
                $itemId = $item->getItemId();
                $item->setProfileId($entity->getProfileId());
                $this->entityManager->save($item);
                if (!$itemId) {
                    foreach ($item->getChildItems() as $childItem) {
                        $childItem
                            ->setProfileId($entity->getProfileId())
                            ->setParentItemId($item->getItemId());
                        $this->entityManager->save($childItem);
                    }
                }
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('Could not save recurring profile.'));
            }
        }
        return $entity;
    }
}
