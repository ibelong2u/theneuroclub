<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel\SubscriptionPlan;

use Aheadworks\Sarp\Model\SubscriptionPlan;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionPlan as SubscriptionPlanResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Sarp\Model\ResourceModel\SubscriptionPlan
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'subscription_plan_id';

    /**
     * @var int
     */
    private $storeId;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(SubscriptionPlan::class, SubscriptionPlanResource::class);
    }

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachDescriptions();
        /** @var SubscriptionPlan $item */
        foreach ($this as $item) {
            $item->setEngineCodes(explode(',', $item->getEngineCodes()));
        }
        return parent::_afterLoad();
    }

    /**
     * Attach descriptions data to collection's items
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function attachDescriptions()
    {
        $ids = $this->getAllIds();
        if (count($ids)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['descriptions_table' => $this->getTable('aw_sarp_subscription_plan_description')])
                ->where('descriptions_table.subscription_plan_id IN (?)', $ids);
            /** @var SubscriptionPlan $item */
            foreach ($this as $item) {
                $descriptions = [];
                $id = $item->getSubscriptionPlanId();
                $storeFrontTitle = null;
                $storeFrontDescription = null;
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data['subscription_plan_id'] == $id) {
                        $descriptions[] = $data;
                        if ($this->storeId && $this->storeId == $data['store_id']) {
                            list($storeFrontTitle, $storeFrontDescription) = [
                                $data['title'],
                                $data['description']
                            ];
                        }
                        if ($data['store_id'] == 0) {
                            if (!$this->storeId) {
                                list($storeFrontTitle, $storeFrontDescription) = [
                                    $data['title'],
                                    $data['description']
                                ];
                            }
                            if (!$storeFrontTitle) {
                                $storeFrontTitle = $data['title'];
                            }
                            if (!$storeFrontDescription) {
                                $storeFrontDescription = $data['description'];
                            }
                        }
                    }
                }
                $item
                    ->setDescriptions($descriptions)
                    ->setStorefrontTitle($storeFrontTitle)
                    ->setStorefrontDescription($storeFrontDescription);
            }
        }
    }
}
