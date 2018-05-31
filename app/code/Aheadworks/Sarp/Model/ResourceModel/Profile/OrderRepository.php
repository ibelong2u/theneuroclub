<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel\Profile;

use Aheadworks\Sarp\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp\Api\Data\ProfileOrderInterfaceFactory;
use Aheadworks\Sarp\Api\Data\ProfileOrderSearchResultsInterface;
use Aheadworks\Sarp\Api\Data\ProfileOrderSearchResultsInterfaceFactory;
use Aheadworks\Sarp\Api\ProfileOrderRepositoryInterface;
use Aheadworks\Sarp\Model\ResourceModel\Profile\Order\Collection as ProfileOrderCollection;
use Aheadworks\Sarp\Model\ResourceModel\Profile\Order\CollectionFactory as ProfileOrderCollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Class OrderRepository
 * @package Aheadworks\Sarp\Model\ResourceModel\Profile
 */
class OrderRepository implements ProfileOrderRepositoryInterface
{
    /**
     * @var ProfileOrderInterfaceFactory
     */
    private $profileOrderFactory;

    /**
     * @var ProfileOrderSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var ProfileOrderCollectionFactory
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
     * @param ProfileOrderInterfaceFactory $profileOrderFactory
     * @param ProfileOrderSearchResultsInterfaceFactory $searchResultsFactory
     * @param ProfileOrderCollectionFactory $collectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ProfileOrderInterfaceFactory $profileOrderFactory,
        ProfileOrderSearchResultsInterfaceFactory $searchResultsFactory,
        ProfileOrderCollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->profileOrderFactory = $profileOrderFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ProfileOrderSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var ProfileOrderCollection $collection */
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, ProfileOrderInterface::class);
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

        $collection
            ->setCurPage($searchCriteria->getCurrentPage())
            ->setPageSize($searchCriteria->getPageSize());

        $profileOrders = [];
        /** @var \Aheadworks\Sarp\Model\Profile\Order $profileOrderModel */
        foreach ($collection as $profileOrderModel) {
            /** @var ProfileOrderInterface $profileOrder */
            $profileOrder = $this->profileOrderFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $profileOrder,
                $profileOrderModel->getData(),
                ProfileOrderInterface::class
            );
            $profileOrders[] = $profileOrder;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($profileOrders)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
