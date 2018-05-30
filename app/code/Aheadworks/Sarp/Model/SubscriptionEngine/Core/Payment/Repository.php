<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment;

use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment as PaymentResource;
use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment\Collection;
use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment\CollectionFactory;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\Repository\CriteriaApplier;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class Repository
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment
 */
class Repository
{
    /**
     * @var PaymentResource
     */
    private $resource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CriteriaApplier
     */
    private $criteriaApplier;

    /**
     * @param PaymentResource $resource
     * @param CollectionFactory $collectionFactory
     * @param CriteriaApplier $criteriaApplier
     */
    public function __construct(
        PaymentResource $resource,
        CollectionFactory $collectionFactory,
        CriteriaApplier $criteriaApplier
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->criteriaApplier = $criteriaApplier;
    }

    /**
     * Save payment instance
     *
     * @param Payment $payment
     * @return Payment
     * @throws CouldNotSaveException
     */
    public function save(Payment $payment)
    {
        try {
            $this->resource->save($payment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $payment;
    }

    /**
     * Get list of payment instances
     *
     * @param array $criteria
     * @return Payment[]
     */
    public function getList(array $criteria)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->criteriaApplier->apply($collection, $criteria);
        return $collection->getItems();
    }

    /**
     * Get list of pending payments for today
     *
     * @return Payment[]
     */
    public function getListOfPendingForToday()
    {
        $criteria = [
            ['status', Payment::STATUS_PENDING],
            ['scheduled_at', 'today']
        ];
        return $this->getList($criteria);
    }

    /**
     * Get list of retrying payments for today
     *
     * @return Payment[]
     */
    public function getListOfRetryingForToday()
    {
        $criteria = [
            ['status', Payment::STATUS_RETRYING],
            ['retry_at', 'today']
        ];
        return $this->getList($criteria);
    }

    /**
     * Check if payment exists
     *
     * @param array $criteria
     * @return bool
     */
    public function has(array $criteria)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->criteriaApplier->apply($collection, $criteria);
        return $collection->getSize() > 0;
    }
}
