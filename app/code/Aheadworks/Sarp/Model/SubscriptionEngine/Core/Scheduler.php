<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core;

use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment as CorePaymentResource;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\DataResolver\NextPaymentDate;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\DataResolver\NextReattemptDate;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\Repository;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\PayableChecker;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Repository as SubscriptionRepository;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Scheduler\PaymentTypesEstimation;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;

/**
 * Class Scheduler
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core
 */
class Scheduler
{
    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var Repository
     */
    private $paymentRepo;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepo;

    /**
     * @var PaymentTypesEstimation
     */
    private $paymentTypeEstimation;

    /**
     * @var PayableChecker
     */
    private $payableChecker;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var CoreDate
     */
    private $coreDate;

    /**
     * @var NextPaymentDate
     */
    private $nextPaymentDate;

    /**
     * @var NextReattemptDate
     */
    private $nextReattemptDate;

    /**
     * @param PaymentFactory $paymentFactory
     * @param Repository $paymentRepo
     * @param SubscriptionRepository $subscriptionRepo
     * @param PaymentTypesEstimation $paymentTypeEstimation
     * @param PayableChecker $payableChecker
     * @param DateTime $dateTime
     * @param CoreDate $coreDate
     * @param NextPaymentDate $nextPaymentDate
     * @param NextReattemptDate $nextReattemptDate
     */
    public function __construct(
        PaymentFactory $paymentFactory,
        Repository $paymentRepo,
        SubscriptionRepository $subscriptionRepo,
        PaymentTypesEstimation $paymentTypeEstimation,
        PayableChecker $payableChecker,
        DateTime $dateTime,
        CoreDate $coreDate,
        NextPaymentDate $nextPaymentDate,
        NextReattemptDate $nextReattemptDate
    ) {
        $this->paymentFactory = $paymentFactory;
        $this->paymentRepo = $paymentRepo;
        $this->subscriptionRepo = $subscriptionRepo;
        $this->paymentTypeEstimation = $paymentTypeEstimation;
        $this->payableChecker = $payableChecker;
        $this->dateTime = $dateTime;
        $this->coreDate = $coreDate;
        $this->nextPaymentDate = $nextPaymentDate;
        $this->nextReattemptDate = $nextReattemptDate;
    }

    /**
     * Perform initial schedule payments
     *
     * @param Subscription $subscription
     * @return Payment[]
     * @throws \Exception
     */
    public function scheduleInitial(Subscription $subscription)
    {
        $payments = [];

        if ($this->canSchedule($subscription)) {
            $paymentTypes = $this->paymentTypeEstimation->estimate(
                $subscription,
                $this->dateTime->formatDate(true),
                null
            );
            foreach ($paymentTypes as $type) {
                $payments[] = $this->schedule(
                    $subscription->getSubscriptionId(),
                    $type,
                    $this->nextPaymentDate->getDateInitial($subscription->getStartDate())
                );
            }
        }

        return $payments;
    }

    /**
     * Schedule next payments from current date. Assumed that current date is a payment date
     *
     * @param Subscription $subscription
     * @param string|null $date
     * @return void
     */
    public function scheduleNext(Subscription $subscription, $date = null)
    {
        if ($this->canSchedule($subscription)) {
            $date = $date ? : $this->dateTime->formatDate(true);
            $nextPaymentDate = $this->nextPaymentDate->getDateNext(
                $date,
                $subscription->getBillingPeriod(),
                $subscription->getBillingFrequency()
            );
            $paymentTypes = $this->paymentTypeEstimation->estimate($subscription, $nextPaymentDate, $date);
            foreach ($paymentTypes as $type) {
                $this->schedule($subscription->getSubscriptionId(), $type, $nextPaymentDate);
            }
        }
    }

    /**
     * Schedule reattempt payment for subscription.
     * This may a new scheduled or existing payment with modified status and schedule date
     *
     * @param Subscription $subscription
     * @param Payment $payment
     * @return Payment[]
     */
    public function scheduleReattempt(Subscription $subscription, Payment $payment)
    {
        $reattempts = [];
        $retries = (int)$payment->getRetriesCount();
        $today = $this->dateTime->formatDate(true);

        $nextPaymentDate = $this->nextPaymentDate->getDateNext(
            $payment->getScheduledAt(),
            $subscription->getBillingPeriod(),
            $subscription->getBillingFrequency()
        );
        $lastRetryDate = $this->nextReattemptDate->getLastDate($today, $retries);
        $nextPaymentDateTm = $this->coreDate->gmtTimestamp($nextPaymentDate);
        $lastRetryDateTm = $this->coreDate->gmtTimestamp($lastRetryDate);
        if ($lastRetryDateTm > $nextPaymentDateTm) {
            $paymentTypes = $this->paymentTypeEstimation->estimate($subscription, $nextPaymentDate, $today);
            foreach ($paymentTypes as $type) {
                $reattempts[] = $this->schedule($subscription->getSubscriptionId(), $type, $nextPaymentDate);
            }
            $payment->setStatus(Payment::STATUS_FAILED);
        } else {
            $nextRetryDate = $this->nextReattemptDate->getDateNext($today, $retries);
            $payment
                ->setStatus(Payment::STATUS_RETRYING)
                ->setRetryAt($nextRetryDate)
                ->setRetriesCount($retries + 1);
            $reattempts[] = $payment;
        }
        $this->paymentRepo->save($payment);
        return $reattempts;
    }

    /**
     * Reschedule outstanding payments
     *
     * @param Subscription $subscription
     * @param Payment $payment
     * @return Payment
     */
    public function reschedule(Subscription $subscription, Payment $payment)
    {
        if ($subscription->getIsReactivated()) {
            $rescheduledDate = $this->nextPaymentDate->getDateNextForOutstanding(
                $payment->getScheduledAt(),
                $subscription->getBillingPeriod(),
                $subscription->getBillingFrequency()
            );
            $subscription->setIsReactivated(false);
            $this->subscriptionRepo->save($subscription);
        } else {
            $rescheduledDate = $this->dateTime->formatDate(true, false);
        }
        $payment->setScheduledAt($rescheduledDate);
        $this->paymentRepo->save($payment);
        return $payment;
    }

    /**
     * Schedule payment
     *
     * @param int $subscriptionId
     * @param string $type
     * @param string $date
     * @return Payment
     * @throws \Exception
     */
    private function schedule($subscriptionId, $type, $date)
    {
        /** @var Payment $payment */
        $payment = $this->paymentFactory->create();
        $payment
            ->setSubscriptionId($subscriptionId)
            ->setStatus(Payment::STATUS_PENDING)
            ->setType($type)
            ->setScheduledAt($date);
        $this->paymentRepo->save($payment);
        return $payment;
    }

    /**
     * Check if payment schedule is available for subscription
     *
     * @param Subscription $subscription
     * @return bool
     */
    private function canSchedule(Subscription $subscription)
    {
        return $this->payableChecker->isPayable($subscription)
            && !$this->paymentRepo->has(
                [
                    ['status', Payment::STATUS_PENDING],
                    ['subscription_id', $subscription->getSubscriptionId()]
                ]
            );
    }
}
