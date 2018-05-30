<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Item;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\BuyRequestProcessor;
use Magento\Catalog\Model\Product;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class SaveHandler
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Item
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var BuyRequestProcessor
     */
    private $buyRequestProcessor;

    /**
     * @param EntityManager $entityManager
     * @param BuyRequestProcessor $buyRequestProcessor
     */
    public function __construct(
        EntityManager $entityManager,
        BuyRequestProcessor $buyRequestProcessor
    ) {
        $this->entityManager = $entityManager;
        $this->buyRequestProcessor = $buyRequestProcessor;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var SubscriptionsCartItemInterface $parentItem */
        $parentItem = null;
        /** @var SubscriptionsCartItemInterface $item */
        foreach ($entity->getInnerItems() as $item) {
            try {
                $itemId = $item->getItemId();
                if ($itemId) {
                    if ($item->getIsDeleted()) {
                        $this->entityManager->delete($item);
                    } else {
                        $buyRequest = $this->buyRequestProcessor->setQty($item->getBuyRequest(), $item->getQty());
                        $item
                            ->setBuyRequest($buyRequest)
                            ->setCartId($entity->getCartId());
                        $this->entityManager->save($item);
                    }
                } elseif (!$item->getIsDeleted()) {
                    $item->setCartId($entity->getCartId());
                    if ($parentItem) {
                        $item->setParentItemId($parentItem->getItemId());
                    }
                    $this->entityManager->save($item);
                    if (!$parentItem) {
                        $parentItem = $item;
                    }
                }
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__($e->getMessage()));
            }
        }
        return $entity;
    }
}
