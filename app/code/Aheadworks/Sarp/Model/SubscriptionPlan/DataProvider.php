<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionPlan;

use Aheadworks\Sarp\Model\ResourceModel\SubscriptionPlan\Collection;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionPlan\CollectionFactory;
use Aheadworks\Sarp\Model\SubscriptionPlan;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 * @package Aheadworks\Sarp\Model\SubscriptionPlan
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $subscriptionPlanCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $subscriptionPlanCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $subscriptionPlanCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var $subscriptionPlan SubscriptionPlan */
        foreach ($items as $subscriptionPlan) {
            $this->loadedData[$subscriptionPlan->getSubscriptionPlanId()] = $this->prepareFormData(
                $subscriptionPlan->getData()
            );
        }

        $data = $this->dataPersistor->get('aw_subscription_plan');
        if (!empty($data)) {
            $subscriptionPlan = $this->collection->getNewEmptyItem();
            $subscriptionPlan->setData($data);
            $this->loadedData[$subscriptionPlan->getSubscriptionPlanId()] = $this->prepareFormData(
                $subscriptionPlan->getData()
            );
            $this->dataPersistor->clear('aw_subscription_plan');
        }

        return $this->loadedData;
    }

    /**
     * Prepare form data
     *
     * @param array $data
     * @return array
     */
    private function prepareFormData($data)
    {
        if ($data['trial_total_billing_cycles'] == 0) {
            $data['trial_total_billing_cycles'] = null;
        }
        return $data;
    }
}
