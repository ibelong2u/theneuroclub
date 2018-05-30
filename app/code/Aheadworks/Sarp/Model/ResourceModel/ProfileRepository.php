<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfileInterfaceFactory;
use Aheadworks\Sarp\Api\Data\ProfileSearchResultsInterface;
use Aheadworks\Sarp\Api\Data\ProfileSearchResultsInterfaceFactory;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\ResourceModel\Profile\Collection;
use Aheadworks\Sarp\Model\ResourceModel\Profile\CollectionFactory;
use Aheadworks\Sarp\Model\ProfileRegistry;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class ProfileRepository
 * @package Aheadworks\Sarp\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProfileRepository implements ProfileRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ProfileRegistry
     */
    private $profileRegistry;

    /**
     * @var ProfileSearchResultsInterfaceFactory
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
     * @param EntityManager $entityManager
     * @param ProfileRegistry $profileRegistry
     * @param ProfileSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        EntityManager $entityManager,
        ProfileRegistry $profileRegistry,
        ProfileSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->entityManager = $entityManager;
        $this->profileRegistry = $profileRegistry;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ProfileInterface $profile, $orderId = null)
    {
        try {
            $this->entityManager->save($profile, ['order_id' => $orderId]);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        $this->profileRegistry->push($profile);
        return $this->get($profile->getProfileId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($profileId)
    {
        return $this->profileRegistry->retrieve($profileId);
    }

    /**
     * {@inheritdoc}
     */
    public function getByReferenceId($referenceId)
    {
        return $this->profileRegistry->retrieveByReferenceId($referenceId);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ProfileSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, ProfileInterface::class);
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

        $profiles = [];
        /** @var \Aheadworks\Sarp\Model\Profile $profileModel */
        foreach ($collection as $profileModel) {
            /** @var ProfileInterface $profile */
            $profile = $this->profileFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $profile,
                $profileModel->getData(),
                ProfileInterface::class
            );
            $profiles[] = $profile;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($profiles)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
