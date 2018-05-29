<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Generator;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\Profile;
use Magento\SalesSequence\Model\Manager as SequenceManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ReferenceId
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Generator
 */
class ReferenceId
{
    /**
     * @var SequenceManager
     */
    private $sequenceManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param SequenceManager $sequenceManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SequenceManager $sequenceManager,
        StoreManagerInterface $storeManager
    ) {
        $this->sequenceManager = $sequenceManager;
        $this->storeManager = $storeManager;
    }

    /**
     * Get profile reference Id
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function getReferenceId(ProfileInterface $profile)
    {
        $store = $this->storeManager->getStore($profile->getStoreId());
        $group = $this->storeManager->getGroup($store->getStoreGroupId());
        $sequence = $this->sequenceManager->getSequence(Profile::ENTITY, $group->getDefaultStoreId());
        return $sequence->getNextValue();
    }
}
