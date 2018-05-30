<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Profile Order CRUD interface
 * @api
 */
interface ProfileOrderRepositoryInterface
{
    /**
     * Retrieve profile orders matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Sarp\Api\Data\ProfileOrderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
