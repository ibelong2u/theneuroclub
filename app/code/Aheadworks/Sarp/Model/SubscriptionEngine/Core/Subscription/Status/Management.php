<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Status;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Exception\OperationIsNotSupportedException;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Repository as SubscriptionRepository;
use Aheadworks\Sarp\Model\Profile\Source\Action;
use Aheadworks\Sarp\Model\Profile\Source\Status;

/**
 * Class Management
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Status
 */
class Management
{
    /**
     * @var ActionValidator
     */
    private $validator;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepo;

    /**
     * @param ActionValidator $validator
     * @param SubscriptionRepository $subscriptionRepo
     */
    public function __construct(
        ActionValidator $validator,
        SubscriptionRepository $subscriptionRepo
    ) {
        $this->validator = $validator;
        $this->subscriptionRepo = $subscriptionRepo;
    }

    /**
     * @param ProfileInterface $profile
     * @param string $action
     * @return string
     * @throws OperationIsNotSupportedException
     */
    public function changeStatus(ProfileInterface $profile, $action)
    {
        if ($this->validator->isValidForAction($profile, $action)) {
            $status = $profile->getStatus();
            switch ($action) {
                case Action::ACTIVATE:
                    $status = Status::ACTIVE;
                    break;
                case Action::CANCEL:
                    $status = Status::CANCELLED;
                    break;
                case Action::SUSPEND:
                    $status = Status::SUSPENDED;
                    break;
                default:
                    break;
            }

            if ($status != $profile->getStatus()) {
                $subscription = $this->subscriptionRepo->getByProfileId($profile->getProfileId());
                $subscription->setIsReactivated($status == Status::ACTIVE);
                $this->subscriptionRepo->save($subscription);
            }
            return $status;
        } else {
            throw new OperationIsNotSupportedException($this->validator->getMessage());
        }
    }
}
