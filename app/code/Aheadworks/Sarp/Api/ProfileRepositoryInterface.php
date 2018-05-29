<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Profile CRUD interface
 * @api
 */
interface ProfileRepositoryInterface
{
    /**
     * Save profile
     *
     * @param \Aheadworks\Sarp\Api\Data\ProfileInterface $profile
     * @param int|null $orderId
     * @return \Aheadworks\Sarp\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Sarp\Api\Data\ProfileInterface $profile, $orderId = null);

    /**
     * Retrieve profile
     *
     * @param int $profileId
     * @return \Aheadworks\Sarp\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($profileId);

    /**
     * Retrieve profile by reference ID
     *
     * @param int $referenceId
     * @return \Aheadworks\Sarp\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByReferenceId($referenceId);

    /**
     * Retrieve profiles matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Sarp\Api\Data\ProfileSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
