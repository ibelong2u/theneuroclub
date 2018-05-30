<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core;

use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Exception\PaymentException;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionResult;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\Repository as PaymentRepository;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\PayableChecker;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Repository as SubscriptionRepository;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class PaymentEngine
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core
 */
class PaymentEngine
{
    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var PaymentProcessor
     */
    private $paymentProcessor;

    /**
     * @var PayableChecker
     */
    private $payableChecker;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepo;

    /**
     * @var PaymentRepository
     */
    private $paymentRepo;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param Scheduler $scheduler
     * @param PaymentProcessor $paymentProcessor
     * @param PayableChecker $payableChecker
     * @param SubscriptionRepository $subscriptionRepo
     * @param PaymentRepository $paymentRepo
     * @param DateTime $dateTime
     */
    public function __construct(
        Scheduler $scheduler,
        PaymentProcessor $paymentProcessor,
        PayableChecker $payableChecker,
        SubscriptionRepository $subscriptionRepo,
        PaymentRepository $paymentRepo,
        DateTime $dateTime
    ) {
        $this->scheduler = $scheduler;
        $this->paymentProcessor = $paymentProcessor;
        $this->payableChecker = $payableChecker;
        $this->subscriptionRepo = $subscriptionRepo;
        $this->paymentRepo = $paymentRepo;
        $this->dateTime = $dateTime;
    }

    /**
     * Make initial payments.
     * Includes initial fee if need or/and first recurring payment if start date is today
     *
     * @param int $profileId
     * @return ActionResult[]
     * @throws PaymentException
     * @api
     */
    public function payInitial($profileId)
    {
        $results = [];
        $subscription = $this->subscriptionRepo->getByProfileId($profileId);
        $payments = $this->scheduler->scheduleInitial($subscription);
        foreach ($payments as $payment) {
            $paymentResult = $this->paymentProcessor->pay($payment, true);
            if ($paymentResult) {
                $results[$payment->getType()] = $paymentResult;
            }
        }
        $this->scheduler->scheduleNext($subscription);
        return $results;
    }

    /**
     * Pay scheduled payments for today
     *
     * @return void
     * @api
     */
    public function payScheduledForToday()
    {
        $today = $this->dateTime->formatDate(true, false);
        $paymentCandidates = $this->paymentRepo->getListOfPendingForToday();
        foreach ($paymentCandidates as $candidate) {
            $subscription = $this->subscriptionRepo->get($candidate->getSubscriptionId());
            if ($candidate->getScheduledAt() < $today
                && $this->payableChecker->isPayable($subscription)
            ) {
                $this->scheduler->reschedule($subscription, $candidate);
            }
            if ($candidate->getScheduledAt() == $today) {
                try {
                    $this->paymentProcessor->pay($candidate);
                } catch (\Exception $e) {
                }
                $this->scheduler->scheduleNext($subscription);
            }
        }
    }

    /**
     * Pay payment reattempts for today
     *
     * @return void
     * @api
     */
    public function payReattemptsForToday()
    {
        /** @var Payment $paymentCandidates */
        $paymentCandidates = array_merge(
            $this->paymentRepo->getListOfRetryingForToday(),
            $this->paymentRepo->getListOfPendingForToday()
        );
        foreach ($paymentCandidates as $candidate) {
            try {
                $this->paymentProcessor->pay($candidate, false, true);
            } catch (\Exception $e) {
            }
            $subscription = $this->subscriptionRepo->get($candidate->getSubscriptionId());
            $this->scheduler->scheduleNext($subscription, $candidate->getScheduledAt());
        }
    }
}
