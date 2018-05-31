<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Profile
 * @package Aheadworks\Sarp\Model\ResourceModel
 */
class Profile extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

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
        $this->_init('aw_sarp_profile', 'profile_id');
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
     * Get profile ID by reference ID
     *
     * @param int $referenceId
     * @return bool|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProfileIdByReferenceId($referenceId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), 'profile_id')
            ->where('reference_id = :referenceId');
        $profileId = $connection->fetchOne($select, ['referenceId' => $referenceId]);
        return $profileId ? (int)$profileId : false;
    }
}
