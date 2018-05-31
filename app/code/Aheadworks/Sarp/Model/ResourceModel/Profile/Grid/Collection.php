<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel\Profile\Grid;

use Aheadworks\Sarp\Model\ResourceModel\Profile\Collection as ProfileCollection;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Framework\App\ResourceConnection;

/**
 * Class Collection
 * @package Aheadworks\Sarp\Model\ResourceModel\Profile\Grid
 */
class Collection extends ProfileCollection implements SearchResultInterface
{
    /**
     * Add order info flag
     *
     * @var bool
     */
    private $addOrderInfoFlag = false;

    /**
     * @var AggregationInterface
     */
    private $aggregations;

    /**
     * @var OrderCollection
     */
    private $orderResource;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param mixed|null $mainTable
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $eventPrefix
     * @param mixed $eventObject
     * @param mixed $resourceModel
     * @param OrderCollection $orderResource
     * @param string $model
     * @param null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        OrderCollection $orderResource,
        $model = Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
        $this->orderResource = $orderResource;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_map['fields']['customer_email'] = 'main_table.customer_email';
        $this->_map['fields']['customer_group_id'] = 'main_table.customer_group_id';
        $this->_map['fields']['status'] = 'main_table.status';
        $this->_map['fields']['created_at'] = 'main_table.created_at';
        $this->setAddOrderInfoFlag();
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->addOrderInfo();
        return parent::_afterLoad();
    }

    /**
     * Add order info on collection loading
     *
     * @return $this
     */
    protected function setAddOrderInfoFlag()
    {
        $this->addOrderInfoFlag = true;
        return $this;
    }

    /**
     * Add order info on collection loading
     *
     * @return $this
     */
    protected function addOrderInfo()
    {
        if ($this->isNeedToAddOrderInfo()) {
            $orderInfo = $this->getOrderInfo();
            $this->addOrderInfoToItems($orderInfo);
        }
        return $this;
    }

    /**
     * Check if need to add order info to collection items
     *
     * @return bool
     */
    private function isNeedToAddOrderInfo()
    {
        $profileIds = $this->getColumnValues($this->getResource()->getIdFieldName());
        return ($this->addOrderInfoFlag && !empty($profileIds));
    }

    /**
     * Retrieve order info for collection items
     *
     * @return array
     */
    private function getOrderInfo()
    {
        $lastOrdersIds = $this->getColumnValues('last_order_id');
        $orderResourceConnection = $this->orderResource->getConnection();
        $select = $orderResourceConnection->select();
        $select->from(
            ['sales_order_table' => $this->orderResource->getTable('sales_order')],
            [
                'last_order_grand_total' => "sales_order_table.base_grand_total",
                'increment_id' => "sales_order_table.increment_id",
                'entity_id',
            ]
        )->where(
            'sales_order_table.entity_id IN(?)',
            $lastOrdersIds
        );
        $orderInfo = $orderResourceConnection->fetchAll($select);
        return $orderInfo;
    }

    /**
     * Add order data to collection items
     *
     * @param array $orderInfo
     * @return $this
     */
    private function addOrderInfoToItems(array $orderInfo)
    {
        foreach ($this->getItems() as $gridItem) {
            $orderItemInfo = $this->getOrderDataForItem($orderInfo, $gridItem->getDataByKey('last_order_id'));
            $gridItem->addData($orderItemInfo);
        }

        return $this;
    }

    /**
     * Get order info for for the specific collection item
     *
     * @param array $orderInfo
     * @param mixed $entityId
     * @return array
     */
    private function getOrderDataForItem($orderInfo, $entityId)
    {
        $defaultOrderInfo = ['last_order_grand_total' => null];
        $orderItemInfo = [];
        foreach ($orderInfo as $orderItem) {
            if ($orderItem['entity_id'] == $entityId) {
                $orderItemInfo = $orderItem;
                break;
            }
        }
        $mergedOrderInfo = array_merge($defaultOrderInfo, $orderItemInfo);
        return $mergedOrderInfo;
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
