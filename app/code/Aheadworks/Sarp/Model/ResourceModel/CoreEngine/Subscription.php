<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel\CoreEngine;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription as SubscriptionModel;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Subscription
 * @package Aheadworks\Sarp\Model\ResourceModel\CoreEngine
 */
class Subscription extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * {@inheritdoc}
     */
    protected $_serializableFields = ['payment_data' => [null, []]];

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sarp_core_subscription', 'subscription_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->_resources->getConnectionByName(
            $this->metadataPool->getMetadata(ProfileInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * Load by profile Id
     *
     * @param SubscriptionModel $subscription
     * @param int $profileId
     * @return $this
     */
    public function loadByProfileId(SubscriptionModel $subscription, $profileId)
    {
        return $this->load($subscription, $profileId, 'profile_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->join(
            ['profile_table' => $this->getTable('aw_sarp_profile')],
            'profile_table.profile_id = ' . $this->getMainTable() . '.profile_id',
            [
                'status' => 'profile_table.status',
                'engine_code' => 'profile_table.engine_code',
                'start_date' => 'profile_table.start_date',
                'is_initial_fee_enabled' => 'profile_table.is_initial_fee_enabled',
                'is_trial_period_enabled' => 'profile_table.is_trial_period_enabled',
                'billing_period' => 'profile_table.billing_period',
                'billing_frequency' => 'profile_table.billing_frequency',
                'total_billing_cycles' => 'profile_table.total_billing_cycles',
                'trial_total_billing_cycles' => 'profile_table.trial_total_billing_cycles'
            ]
        );
        return $select;
    }
}
