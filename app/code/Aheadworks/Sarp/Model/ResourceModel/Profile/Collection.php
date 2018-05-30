<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel\Profile;

use Aheadworks\Sarp\Model\Profile;
use Aheadworks\Sarp\Model\ResourceModel\Profile as ProfileResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Sarp\Model\ResourceModel\Profile
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'profile_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Profile::class, ProfileResource::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachItems();
        return parent::_afterLoad();
    }

    /**
     * Attach profile items data to collection
     *
     * @return void
     */
    private function attachItems()
    {
        $profileIds = $this->getColumnValues('profile_id');
        if (count($profileIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['profile_item_table' => $this->getTable('aw_sarp_profile_item')])
                ->where('profile_item_table.profile_id IN (?)', $profileIds)
                ->where('profile_item_table.parent_item_id IS NULL');
            $itemsData = $connection->fetchAll($select);

            /** @var \Magento\Framework\DataObject $profile */
            foreach ($this as $profile) {
                $profileId = $profile->getData('profile_id');
                $items = [];
                foreach ($itemsData as $data) {
                    if ($data['profile_id'] == $profileId) {
                        $items[] = $data;
                    }
                }
                $profile->setData('items', $items);
            }
        }
    }
}
