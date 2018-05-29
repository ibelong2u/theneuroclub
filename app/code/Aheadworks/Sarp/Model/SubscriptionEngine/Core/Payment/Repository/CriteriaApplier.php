<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\Repository;

use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment\Collection;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class CriteriaApplier
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\Repository
 */
class CriteriaApplier
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param DateTime $dateTime
     */
    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * Apply search criteria to collection
     *
     * @param Collection $collection
     * @param array $criteria
     */
    public function apply(Collection $collection, array $criteria)
    {
        foreach ($criteria as $filter) {
            list($field, $value) = $filter;
            if (is_array($field)) {
                $fields = $field;
                $values = $value;
            } else {
                $fields = [$field];
                $values = [$value];
            }

            $conditions = [];
            foreach ($fields as $index => $filterField) {
                $cond = 'eq';
                if (is_array($value)) {
                    $cond = 'in';
                }
                if (in_array($field, ['scheduled_at', 'retry_at'])) {
                    $cond = 'lteq';
                    if ($values[$index] == 'today') {
                        $values[$index] = $this->dateTime->formatDate(true, false);
                    }
                }

                $conditions[] = [$cond => $values[$index]];
            }

            if ($fields) {
                if (count($fields) > 1) {
                    $collection->addFieldToFilter($fields, $conditions);
                } else {
                    $collection->addFieldToFilter($fields[0], $conditions[0]);
                }
            }
        }
    }
}
