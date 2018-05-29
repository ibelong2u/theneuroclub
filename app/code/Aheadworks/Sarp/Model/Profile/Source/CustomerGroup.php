<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Profile\Source;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class CustomerGroup
 * @package Aheadworks\Sarp\Model\Profile\Source
 */
class CustomerGroup implements ArrayInterface
{
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var array
     */
    private $options;

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $groups = $this->groupRepository->getList($this->searchCriteriaBuilder->create());
            foreach ($groups->getItems() as $group) {
                $groupId = $group->getId();
                $label = $groupId == GroupInterface::NOT_LOGGED_IN_ID
                    ? 'GUEST'
                    : $group->getCode();
                $this->options[] = ['value' => $groupId, 'label' => $label];
            }
        }
        return $this->options;
    }
}
