<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel\Logger\LogEntry\Grid;

use Aheadworks\Sarp\Model\ResourceModel\Logger\LogEntry\Collection as LogEntryCollection;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;

/**
 * Class Collection
 * @package Aheadworks\Sarp\Model\ResourceModel\Logger\LogEntry\Grid
 */
class Collection extends LogEntryCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param mixed|null $mainTable
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $eventPrefix
     * @param mixed $eventObject
     * @param mixed $resourceModel
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
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_map['fields']['engine_code'] = $this->getMainTableAlias() . '.engine_code';
        $this->_map['fields']['profile_reference_id'] = 'profile_table.reference_id';
        $this->_map['fields']['customer_info'] = new \Zend_Db_Expr($this->getCustomerInfoExpression());
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            [$this->getCustomerTableAlias() => $this->getTable('customer_entity')],
            $this->getCustomerTableAlias() . '.entity_id = ' . $this->getMainTableAlias() . '.customer_id'
        );
        $this->getSelect()->joinLeft(
            ['profile_table' => $this->getTable('aw_sarp_profile')],
            'profile_table.profile_id = ' . $this->getMainTableAlias() . '.profile_id',
            [
                'profile_reference_id' => 'profile_table.reference_id',
                'customer_info' => $this->getCustomerInfoExpression()
            ]
        );
        return $this;
    }

    /**
     * Retrieve SQL expression for customer info
     *
     * @return string
     */
    private function getCustomerInfoExpression()
    {
        return 'COALESCE(
                    ' . $this->getMainTableAlias() . '.customer_fullname, 
                    CONCAT_WS(
                        \' \',
                        ' . $this->getCustomerTableAlias() . '.prefix,
                        ' . $this->getCustomerTableAlias() . '.firstname, 
                        ' . $this->getCustomerTableAlias() . '.middlename,
                        ' . $this->getCustomerTableAlias() . '.lastname,
                        ' . $this->getCustomerTableAlias() . '.suffix
                    )
                )';
    }

    /**
     * Retrieve SQL alias for log table
     *
     * @return string
     */
    private function getMainTableAlias()
    {
        return 'main_table';
    }

    /**
     * Retrieve SQL alias for customer entity table
     *
     * @return string
     */
    private function getCustomerTableAlias()
    {
        return 'customer_table';
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     * SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
