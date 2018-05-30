<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Status;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\Profile\Source\Action;
use Aheadworks\Sarp\Model\Profile\Source\Status;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\Repository as PaymentRepository;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Repository as SubscriptionRepository;
use Magento\Framework\Phrase;

/**
 * Class ActionValidator
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Status
 */
class ActionValidator
{
    /**
     * @var Phrase
     */
    private $message;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepo;

    /**
     * @var PaymentRepository
     */
    private $paymentRepo;

    /**
     * @param SubscriptionRepository $subscriptionRepo
     * @param PaymentRepository $paymentRepo
     */
    public function __construct(
        SubscriptionRepository $subscriptionRepo,
        PaymentRepository $paymentRepo
    ) {
        $this->subscriptionRepo = $subscriptionRepo;
        $this->paymentRepo = $paymentRepo;
    }

    /**
     * Check if profile is valid for action
     *
     * @param ProfileInterface $profile
     * @param string $action
     * @return bool
     */
    public function isValidForAction(ProfileInterface $profile, $action)
    {
        $this->message = null;

        if ($action == Action::ACTIVATE && $profile->getStatus() == Status::SUSPENDED) {
            $subscription = $this->subscriptionRepo->getByProfileId($profile->getProfileId());
            if ($subscription->getPaymentFailuresCount() > 0) {
                $hasPending = $this->paymentRepo->has(
                    [
                        ['subscription_id', $subscription->getSubscriptionId()],
                        ['status', Payment::STATUS_PENDING]
                    ]
                );
                $hasRetrying = $this->paymentRepo->has(
                    [
                        ['subscription_id', $subscription->getSubscriptionId()],
                        ['status', Payment::STATUS_RETRYING]
                    ]
                );
                if ($hasPending || $hasRetrying) {
                    $this->message = __(
                        'Unable to perform %1 action. %2',
                        $action,
                        __('Subscription suspended due to payment failures.')
                    );
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get validation message
     *
     * @return Phrase
     */
    public function getMessage()
    {
        return $this->message;
    }
}
