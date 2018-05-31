<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterfaceFactory;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\TotalsCollector;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart as SubscriptionsCartResource;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SubscriptionsCartRepository
 * @package Aheadworks\Sarp\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriptionsCartRepository implements SubscriptionsCartRepositoryInterface
{
    /**
     * @var array
     */
    private $instancesById = [];

    /**
     * @var array
     */
    private $instancesByCustomerId = [];

    /**
     * @var array
     */
    private $sharedStoreIds;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SubscriptionsCartInterfaceFactory
     */
    private $subscriptionsCartFactory;

    /**
     * @var SubscriptionsCartResource
     */
    private $subscriptionsCartResource;

    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @param EntityManager $entityManager
     * @param StoreManagerInterface $storeManager
     * @param SubscriptionsCartInterfaceFactory $subscriptionsCartFactory
     * @param SubscriptionsCart $subscriptionsCartResource
     * @param TotalsCollector $totalsCollector
     */
    public function __construct(
        EntityManager $entityManager,
        StoreManagerInterface $storeManager,
        SubscriptionsCartInterfaceFactory $subscriptionsCartFactory,
        SubscriptionsCartResource $subscriptionsCartResource,
        TotalsCollector $totalsCollector
    ) {
        $this->entityManager = $entityManager;
        $this->storeManager = $storeManager;
        $this->subscriptionsCartFactory = $subscriptionsCartFactory;
        $this->subscriptionsCartResource = $subscriptionsCartResource;
        $this->totalsCollector = $totalsCollector;
    }

    /**
     * {@inheritdoc}
     */
    public function save(SubscriptionsCartInterface $cart, $recollectTotals = true)
    {
        try {
            if ($recollectTotals) {
                $this->totalsCollector->collect($cart);
            }
            $this->entityManager->save($cart);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instancesById[$cart->getCartId()]);
        if ($cart->getCustomerId()) {
            unset($this->instancesByCustomerId[$cart->getCustomerId()]);
        }
        return $this->get($cart->getCartId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        if (!isset($this->instancesById[$cartId])) {
            /** @var SubscriptionsCartInterface $cart */
            $cart = $this->subscriptionsCartFactory->create();
            $this->entityManager->load($cart, $cartId, ['storeIds' => $this->getSharedStoreIds()]);
            if (!$cart->getCartId() || !in_array($cart->getStoreId(), $this->getSharedStoreIds())) {
                throw NoSuchEntityException::singleField('cartId', $cartId);
            }
            $this->instancesById[$cartId] = $cart;
        }
        return $this->instancesById[$cartId];
    }

    /**
     * {@inheritdoc}
     */
    public function getActive($cartId)
    {
        $cart = $this->get($cartId);
        if (!$cart->getIsActive()) {
            throw NoSuchEntityException::singleField('cartId', $cartId);
        }
        return $cart;
    }

    /**
     * {@inheritdoc}
     */
    public function getForCustomer($customerId)
    {
        if (!isset($this->instancesByCustomerId [$customerId])) {
            $cartId = $this->subscriptionsCartResource->getCartIdByCustomerId($customerId);
            if (!$cartId) {
                throw NoSuchEntityException::singleField('customerId', $customerId);
            }
            $this->instancesByCustomerId[$customerId] = $this->get($cartId);
        }
        return $this->instancesByCustomerId[$customerId];
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveForCustomer($customerId)
    {
        $cart = $this->getForCustomer($customerId);
        if (!$cart->getIsActive()) {
            throw NoSuchEntityException::singleField('customerId', $customerId);
        }
        return $cart;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(SubscriptionsCartInterface $cart)
    {
        $cartId = $cart->getCartId();
        $customerId = $cart->getCustomerId();
        $this->entityManager->delete($cart);
        unset($this->instancesById[$cartId]);
        if ($cart->getCustomerId()) {
            unset($this->instancesByCustomerId[$customerId]);
        }
        return true;
    }

    /**
     * Get all available store ids for subscriptions cart
     *
     * @return array
     */
    private function getSharedStoreIds()
    {
        if (!$this->sharedStoreIds) {
            $this->sharedStoreIds = [];
            $website = $this->storeManager->getWebsite();
            foreach ($this->storeManager->getStores() as $store) {
                if ($store->getWebsiteId() == $website->getId()) {
                    $this->sharedStoreIds[] = $store->getId();
                }
            }
        }
        return $this->sharedStoreIds;
    }
}
