<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterfaceFactory;
use Aheadworks\Sarp\Api\Data\SubscriptionPlanSearchResultsInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionPlanSearchResultsInterfaceFactory;
use Aheadworks\Sarp\Api\SubscriptionPlanRepositoryInterface;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionPlan\Collection;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionPlan\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SubscriptionPlanRepository
 * @package Aheadworks\Sarp\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriptionPlanRepository implements SubscriptionPlanRepositoryInterface
{
    /**
     * @var SubscriptionPlanInterface[]
     */
    private $instances = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var SubscriptionPlanInterfaceFactory
     */
    private $subscriptionPlanFactory;

    /**
     * @var SubscriptionPlanSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param EntityManager $entityManager
     * @param SubscriptionPlanInterfaceFactory $subscriptionPlanFactory
     * @param SubscriptionPlanSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        EntityManager $entityManager,
        SubscriptionPlanInterfaceFactory $subscriptionPlanFactory,
        SubscriptionPlanSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->entityManager = $entityManager;
        $this->subscriptionPlanFactory = $subscriptionPlanFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(SubscriptionPlanInterface $subscriptionPlan)
    {
        try {
            $this->entityManager->save($subscriptionPlan);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        unset($this->instances[$subscriptionPlan->getSubscriptionPlanId()]);
        return $this->get($subscriptionPlan->getSubscriptionPlanId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($subscriptionPlanId)
    {
        if (!isset($this->instances[$subscriptionPlanId])) {
            /** @var SubscriptionPlanInterface $subscriptionPlan */
            $subscriptionPlan = $this->subscriptionPlanFactory->create();
            $storeId = $this->storeManager->getStore()->getId();
            $arguments = $storeId == Store::DEFAULT_STORE_ID
                ? []
                : ['store_id' => $storeId];
            $this->entityManager->load($subscriptionPlan, $subscriptionPlanId, $arguments);
            if (!$subscriptionPlan->getSubscriptionPlanId()) {
                throw NoSuchEntityException::singleField('subscriptionPlanId', $subscriptionPlanId);
            }
            $this->instances[$subscriptionPlanId] = $subscriptionPlan;
        }
        return $this->instances[$subscriptionPlanId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var SubscriptionPlanSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, SubscriptionPlanInterface::class);
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        if ($sortOrders = $searchCriteria->getSortOrders()) {
            /** @var \Magento\Framework\Api\SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }
        $storeId = $this->storeManager->getStore()->getId();
        if ($storeId != Store::DEFAULT_STORE_ID) {
            $collection->setStoreId($storeId);
        }

        $plans = [];
        /** @var \Aheadworks\Sarp\Model\SubscriptionPlan $planModel */
        foreach ($collection as $planModel) {
            /** @var SubscriptionPlanInterface $plan */
            $plan = $this->subscriptionPlanFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $plan,
                $planModel->getData(),
                SubscriptionPlanInterface::class
            );
            $plans[] = $plan;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($plans)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(SubscriptionPlanInterface $subscriptionPlan)
    {
        return $this->deleteById($subscriptionPlan->getSubscriptionPlanId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($subscriptionPlanId)
    {
        /** @var SubscriptionPlanInterface $subscriptionPlan */
        $subscriptionPlan = $this->subscriptionPlanFactory->create();
        $this->entityManager->load($subscriptionPlan, $subscriptionPlanId);
        if (!$subscriptionPlan->getSubscriptionPlanId()) {
            throw NoSuchEntityException::singleField('subscriptionPlanId', $subscriptionPlanId);
        }
        $this->entityManager->delete($subscriptionPlan);
        unset($this->instances[$subscriptionPlanId]);
        return true;
    }
}
