<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart;

use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartAddressRepositoryInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterfaceFactory;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressSearchResultsInterface as SearchResults;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressSearchResultsInterfaceFactory as SearchResultsFactory;
use Aheadworks\Sarp\Model\SubscriptionsCart\TotalsCollector;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AddressRepository
 * @package Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepository implements SubscriptionsCartAddressRepositoryInterface
{
    /**
     * @var array
     */
    private $instances = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var SubscriptionsCartAddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var SubscriptionsCartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @param EntityManager $entityManager
     * @param SubscriptionsCartAddressInterfaceFactory $addressFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param SubscriptionsCartRepositoryInterface $cartRepository
     * @param TotalsCollector $totalsCollector
     */
    public function __construct(
        EntityManager $entityManager,
        SubscriptionsCartAddressInterfaceFactory $addressFactory,
        SearchResultsFactory $searchResultsFactory,
        SubscriptionsCartRepositoryInterface $cartRepository,
        TotalsCollector $totalsCollector
    ) {
        $this->entityManager = $entityManager;
        $this->addressFactory = $addressFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->cartRepository = $cartRepository;
        $this->totalsCollector = $totalsCollector;
    }

    /**
     * {@inheritdoc}
     */
    public function save(SubscriptionsCartAddressInterface $address)
    {
        try {
            $this->entityManager->save($address);
            if ($address->getCartId()) {
                $cart = $this->cartRepository->getActive($address->getCartId());
                $this->totalsCollector->collect($cart);
                $this->entityManager->save($cart);
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$address->getAddressId()]);
        return $this->get($address->getAddressId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($addressId)
    {
        if (!isset($this->instances[$addressId])) {
            /** @var SubscriptionsCartAddressInterface $address */
            $address = $this->addressFactory->create();
            $this->entityManager->load($address, $addressId);
            if (!$address->getAddressId()) {
                throw NoSuchEntityException::singleField('addressId', $addressId);
            }
            $this->instances[$addressId] = $address;
        }
        return $this->instances[$addressId];
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
            ->setItems($cart->getAddresses())
            ->setTotalCount(count($cart->getAddresses()));
        return $searchResults;
    }
}
