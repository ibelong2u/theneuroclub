<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Observer;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterfaceFactory;
use Aheadworks\Sarp\Api\SubscriptionsCartManagementInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor as CartPersistor;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class LoadCustomerCartObserver
 * @package Aheadworks\Sarp\Observer
 */
class LoadCustomerCartObserver implements ObserverInterface
{
    /**
     * @var CartPersistor
     */
    private $cartPersistor;

    /**
     * @var SubscriptionsCartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var SubscriptionsCartInterfaceFactory
     */
    private $cartFactory;

    /**
     * @var SubscriptionsCartManagementInterface
     */
    private $cartManagement;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param CartPersistor $cartPersistor
     * @param SubscriptionsCartRepositoryInterface $cartRepository
     * @param SubscriptionsCartInterfaceFactory $cartFactory
     * @param SubscriptionsCartManagementInterface $cartManagement
     * @param CustomerSession $customerSession
     */
    public function __construct(
        CartPersistor $cartPersistor,
        SubscriptionsCartRepositoryInterface $cartRepository,
        SubscriptionsCartInterfaceFactory $cartFactory,
        SubscriptionsCartManagementInterface $cartManagement,
        CustomerSession $customerSession
    ) {
        $this->cartPersistor = $cartPersistor;
        $this->cartRepository = $cartRepository;
        $this->cartFactory = $cartFactory;
        $this->cartManagement = $cartManagement;
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            try {
                $customerCart = $this->cartRepository->getForCustomer($customerId);
            } catch (NoSuchEntityException $e) {
                /** @var SubscriptionsCartInterface $customerCart */
                $customerCart = $this->cartFactory->create();
            }
            $currentCart = $this->cartPersistor->getSubscriptionCart();

            if ($customerCart->getCartId()
                && $customerCart->getCartId() != $this->cartPersistor->getCartId()
            ) {
                if ($this->cartPersistor->getCartId()) {
                    $this->cartManagement->merge($customerCart, $currentCart);
                }
                $this->cartPersistor->setCartId($customerCart->getCartId());

                if ($currentCart->getCartId()) {
                    $this->cartRepository->delete($currentCart);
                }
            } else {
                $currentCart->setCustomerId($customerId);
                $this->cartRepository->save($currentCart);
            }
        }
    }
}
