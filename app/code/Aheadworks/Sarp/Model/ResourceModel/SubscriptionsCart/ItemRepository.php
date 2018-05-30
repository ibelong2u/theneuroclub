<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterfaceFactory;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemSearchResultsInterface as SearchResults;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemSearchResultsInterfaceFactory as SearchResultsFactory;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartItemRepositoryInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\ItemsComparator;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ItemRepository
 * @package Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemRepository implements SubscriptionsCartItemRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var SubscriptionsCartItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var SubscriptionsCartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ItemsComparator
     */
    private $itemComparator;

    /**
     * @param EntityManager $entityManager
     * @param SubscriptionsCartItemInterfaceFactory $itemFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param SubscriptionsCartRepositoryInterface $cartRepository
     * @param ItemsComparator $itemComparator
     */
    public function __construct(
        EntityManager $entityManager,
        SubscriptionsCartItemInterfaceFactory $itemFactory,
        SearchResultsFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        SubscriptionsCartRepositoryInterface $cartRepository,
        ItemsComparator $itemComparator
    ) {
        $this->entityManager = $entityManager;
        $this->itemFactory = $itemFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->cartRepository = $cartRepository;
        $this->itemComparator = $itemComparator;
    }

    /**
     * {@inheritdoc}
     */
    public function save(SubscriptionsCartItemInterface $item)
    {
        $cart = $this->cartRepository->getActive($item->getCartId());
        $itemInCart = $this->findInCart($cart, $item);
        try {
            if ($itemInCart) {
                $this->dataObjectHelper->mergeDataObjects(
                    SubscriptionsCartItemInterface::class,
                    $itemInCart,
                    $item
                );
                $this->cartRepository->save($cart);
            } else {
                $this->entityManager->save($item);
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $cart;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($cartId)
    {
        $cart = $this->cartRepository->getActive($cartId);
        /** @var SearchResults $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults
            ->setItems($cart->getItems())
            ->setTotalCount(count($cart->getItems()));
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($cartId, $itemId)
    {
        $cart = $this->cartRepository->getActive($cartId);
        /** @var SubscriptionsCartItemInterface $item */
        $item = $this->itemFactory->create();
        $this->entityManager->load($item, $itemId);

        $itemInCart = $this->findInCart($cart, $item);
        if (!$itemInCart) {
            throw new NoSuchEntityException(
                __('Cart %1 doesn\'t contain item %2.', $cartId, $itemId)
            );
        }
        $itemInCart->setIsDeleted(true);
        foreach ($cart->getInnerItems() as $innerItem) {
            if ($innerItem->getParentItemId() == $itemInCart->getItemId()) {
                $innerItem->setIsDeleted(true);
            }
        }

        try {
            $this->cartRepository->save($cart);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not remove item from cart.'));
        }

        return $cart;
    }

    /**
     * Find the same item in cart
     *
     * @param SubscriptionsCartInterface $cart
     * @param SubscriptionsCartItemInterface $item
     * @return SubscriptionsCartItemInterface|bool
     */
    private function findInCart(SubscriptionsCartInterface $cart, SubscriptionsCartItemInterface $item)
    {
        foreach ($cart->getItems() as $cartItem) {
            if ($this->itemComparator->isEquals($cartItem, $item)) {
                return $cartItem;
            }
        }
        return false;
    }
}
