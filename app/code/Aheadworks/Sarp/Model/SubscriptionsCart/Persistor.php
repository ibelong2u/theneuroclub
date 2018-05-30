<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterfaceFactory;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Model\Session as SarpSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Persistor
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Persistor
{
    /**
     * @var SarpSession
     */
    private $sarpSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SubscriptionsCartInterfaceFactory
     */
    private $subscriptionsCartFactory;

    /**
     * @var SubscriptionsCartRepositoryInterface
     */
    private $subscriptionsCartRepository;

    /**
     * @var SubscriptionsCartInterface
     */
    private $subscriptionsCart;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @param SarpSession $sarpSession
     * @param StoreManagerInterface $storeManager
     * @param SubscriptionsCartInterfaceFactory $subscriptionsCartFactory
     * @param SubscriptionsCartRepositoryInterface $subscriptionsCartRepository
     * @param CustomerSession $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param Copy $objectCopyService
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        SarpSession $sarpSession,
        StoreManagerInterface $storeManager,
        SubscriptionsCartInterfaceFactory $subscriptionsCartFactory,
        SubscriptionsCartRepositoryInterface $subscriptionsCartRepository,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        Copy $objectCopyService,
        RemoteAddress $remoteAddress
    ) {
        $this->sarpSession = $sarpSession;
        $this->storeManager = $storeManager;
        $this->subscriptionsCartFactory = $subscriptionsCartFactory;
        $this->subscriptionsCartRepository = $subscriptionsCartRepository;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->objectCopyService = $objectCopyService;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * Get subscriptions cart by current session
     *
     * @return SubscriptionsCartInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getSubscriptionCart()
    {
        if (!$this->subscriptionsCart) {
            $storeId = $this->storeManager->getStore()->getId();
            if ($this->getCartId()) {
                /** @var SubscriptionsCartInterface $subscriptionsCart */
                $subscriptionsCart = $this->loadActive($this->getCartId())
                    ?
                    : ($this->loadActiveForCustomer() ? : $this->subscriptionsCartFactory->create());

                if (!$subscriptionsCart->getCartId()) {
                    $this->setCartId(null);
                } else {
                    $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
                    if ($subscriptionsCart->getCartCurrencyCode() != $currentCurrencyCode) {
                        $subscriptionsCart->setStoreId($storeId);
                        $this->subscriptionsCartRepository->save($subscriptionsCart);
                        $subscriptionsCart = $this->subscriptionsCartRepository->get(
                            $subscriptionsCart->getCartId()
                        );
                    }
                }
            } else {
                /** @var SubscriptionsCartInterface $subscriptionsCart */
                $subscriptionsCart = $this->loadActiveForCustomer() ? : $this->subscriptionsCartFactory->create();
                if ($subscriptionsCart->getCartId()) {
                    $this->setCartId($subscriptionsCart->getCartId());
                }
            }

            if ($this->customerSession->isLoggedIn()) {
                $customerId = $this->customerSession->getCustomerId();
                $subscriptionsCart->setCustomerId($customerId);
                $this->objectCopyService->copyFieldsetToTarget(
                    'aw_sarp_customer',
                    'to_cart',
                    $this->customerRepository->getById($customerId),
                    $subscriptionsCart
                );
                $subscriptionsCart->setCustomerIsGuest(false);
            }
            $subscriptionsCart->setStoreId($storeId);

            $this->subscriptionsCart = $subscriptionsCart;
        }

        $remoteAddress = $this->remoteAddress->getRemoteAddress();
        if ($remoteAddress) {
            $this->subscriptionsCart->setRemoteIp($remoteAddress);
        }

        return $this->subscriptionsCart;
    }

    /**
     * Get cart ID
     *
     * @return int
     */
    public function getCartId()
    {
        return $this->sarpSession->getCartId(
            $this->storeManager->getStore()->getWebsiteId()
        );
    }

    /**
     * Set cart ID
     *
     * @param int|null $cartId
     * @return void
     */
    public function setCartId($cartId)
    {
        $this->sarpSession->setCartId(
            $cartId,
            $this->storeManager->getStore()->getWebsiteId()
        );
    }

    /**
     * Clear data
     *
     * @return void
     */
    public function clear()
    {
        $this->subscriptionsCart = null;
        $this->setCartId(null);
        $this->sarpSession
            ->setLastSuccessCartId(null)
            ->setLastProfileId(null);
    }

    /**
     * Try to load active cart by cart ID
     *
     * @param int $cartId
     * @return SubscriptionsCartInterface|bool
     */
    private function loadActive($cartId)
    {
        try {
            return $this->subscriptionsCartRepository->getActive($cartId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Try to load active cart for customer if customer is logged in
     *
     * @return SubscriptionsCartInterface|bool
     */
    private function loadActiveForCustomer()
    {
        if ($this->customerSession->isLoggedIn()) {
            try {
                return $this->subscriptionsCartRepository->getActiveForCustomer(
                    $this->customerSession->getCustomerId()
                );
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
}
