<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core;

use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\Logger\LoggerInterface;
use Aheadworks\Sarp\Model\Profile\PaymentInfo;
use Aheadworks\Sarp\Model\Profile\Source\Status;
use Aheadworks\Sarp\Model\ProfileRegistry;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Exception\PaymentException;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Exception\PaymentActionException;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionPool;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionResult;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\PaymentInfoBuilder;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\Repository as PaymentRepository;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\ExpirationChecker;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\PayableChecker;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Repository as SubscriptionRepository;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\SecureDataFilter;
use Magento\Framework\EntityManager\EntityManager;

/**
 * Class PaymentProcessor
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core
 */
class PaymentProcessor
{
    /**
     * Maximum payment failures count
     */
    const MAX_PAYMENT_FAILURES = 3;

    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepo;

    /**
     * @var PaymentRepository
     */
    private $paymentRepo;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ProfileRegistry
     */
    private $profileRegistry;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ActionPool
     */
    private $paymentActionPool;

    /**
     * @var PaymentInfoBuilder
     */
    private $paymentInfoBuilder;

    /**
     * @var PayableChecker
     */
    private $payableChecker;

    /**
     * @var ExpirationChecker
     */
    private $expirationChecker;

    /**
     * @var SecureDataFilter
     */
    private $secureDataFilter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Scheduler $scheduler
     * @param SubscriptionRepository $subscriptionRepo
     * @param PaymentRepository $paymentRepo
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileRegistry $profileRegistry
     * @param EntityManager $entityManager
     * @param ActionPool $paymentActionPool
     * @param PaymentInfoBuilder $paymentInfoBuilder
     * @param PayableChecker $payableChecker
     * @param ExpirationChecker $expirationChecker
     * @param SecureDataFilter $secureDataFilter
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Scheduler $scheduler,
        SubscriptionRepository $subscriptionRepo,
        PaymentRepository $paymentRepo,
        ProfileRepositoryInterface $profileRepository,
        ProfileRegistry $profileRegistry,
        EntityManager $entityManager,
        ActionPool $paymentActionPool,
        PaymentInfoBuilder $paymentInfoBuilder,
        PayableChecker $payableChecker,
        ExpirationChecker $expirationChecker,
        SecureDataFilter $secureDataFilter,
        LoggerInterface $logger
    ) {
        $this->scheduler = $scheduler;
        $this->subscriptionRepo = $subscriptionRepo;
        $this->paymentRepo = $paymentRepo;
        $this->profileRepository = $profileRepository;
        $this->profileRegistry = $profileRegistry;
        $this->entityManager = $entityManager;
        $this->paymentActionPool = $paymentActionPool;
        $this->paymentInfoBuilder = $paymentInfoBuilder;
        $this->payableChecker = $payableChecker;
        $this->expirationChecker = $expirationChecker;
        $this->secureDataFilter = $secureDataFilter;
        $this->logger = $logger;
    }

    /**
     * Perform payment
     *
     * @param Payment $payment
     * @param bool $isInitial
     * @param bool $isReattempt
     * @throws PaymentException
     * @return ActionResult|null
     */
    public function pay(Payment $payment, $isInitial = false, $isReattempt = false)
    {
        $subscription = $this->subscriptionRepo->get($payment->getSubscriptionId());
        if ($this->payableChecker->isPayable($subscription, $isReattempt)) {
            $profileId = $subscription->getProfileId();
            $profile = $this->profileRepository->get($profileId);
            $paymentInfo = $this->paymentInfoBuilder
                ->setProfile($profile)
                ->setPaymentType($payment->getType())
                ->build();

            $action = $this->paymentActionPool->getAction(
                $profile->getEngineCode(),
                $profile->getPaymentMethodCode()
            );
            try {
                if (!$isInitial && $payment->getStatus() != Payment::STATUS_RETRYING) {
                    $this->logger->notice($profile, LoggerInterface::ENTRY_TYPE_PAYMENT_STARTED);
                }

                $result = $action->pay($profile, $paymentInfo, $subscription->getPaymentData());
                $payment->setStatus($result->getStatus());
                if ($result->getOrder()) {
                    $payment->setOrderId($result->getOrder()->getEntityId());
                    if (!$isInitial) {
                        $this->logger->notice(
                            $profile,
                            LoggerInterface::ENTRY_TYPE_PAYMENT_AUTHORIZED,
                            ['order' => $result->getOrder()]
                        );
                    }
                }

                $this->paymentRepo->save($payment);
                if ($isReattempt) {
                    $profile->setStatus(Status::ACTIVE);
                    $subscription->setPaymentFailuresCount(0);
                }
            } catch (PaymentActionException $e) {
                $loggerAddInfo = ['exception' => $e];

                $failures = $subscription->getPaymentFailuresCount();
                $failures++;
                $subscription->setPaymentFailuresCount($failures);
                if ($failures >= self::MAX_PAYMENT_FAILURES) {
                    $profile->setStatus(Status::CANCELLED);
                    $payment->setStatus(Payment::STATUS_FAILED);
                    $this->paymentRepo->save($payment);

                    $loggerAddInfo['statusChanged'] = true;
                } else {
                    $profile->setStatus(Status::SUSPENDED);
                    $reattempts = $this->scheduler->scheduleReattempt($subscription, $payment);

                    if (count($reattempts) == 0 && $this->expirationChecker->isExpire($subscription, $profile)) {
                        $profile->setStatus(Status::EXPIRED);
                        $loggerAddInfo['statusChanged'] = true;
                    }
                    $loggerAddInfo['reattempts'] = $reattempts;
                }
                $this->entityManager->save($profile, ['coreSubscriptionToUpdate' => $subscription]);
                if (!$isInitial) {
                    $this->logger->error($profile, LoggerInterface::ENTRY_TYPE_PAYMENT_FAIL, $loggerAddInfo);
                }

                throw new PaymentException(__('Payment has been failed: %1', $e->getMessage()));
            } catch (\Exception $e) {
                $payment
                    ->setStatus(Payment::STATUS_CANCELLED);
                $this->paymentRepo->save($payment);
                throw new PaymentException(__('Something went wrong while perform payment'));
            }

            $paymentType = $payment->getType();
            if ($paymentType == PaymentInfo::PAYMENT_TYPE_INITIAL) {
                $subscription->setIsInitialPaid(true);
                $profile->setStatus(Status::ACTIVE);
            } elseif ($paymentType == PaymentInfo::PAYMENT_TYPE_TRIAL) {
                $subscription->setTrialPaymentsCount($subscription->getTrialPaymentsCount() + 1);
            } else {
                $subscription->setRegularPaymentsCount($subscription->getRegularPaymentsCount() + 1);
            }

            $paymentData = $this->secureDataFilter->filter(
                $subscription->getPaymentData(),
                $profile->getPaymentMethodCode()
            );
            $subscription->setPaymentData($paymentData);

            if ($this->expirationChecker->isExpire($subscription, $profile)) {
                $profile->setStatus(Status::EXPIRED);
                $this->logger->notice($profile, LoggerInterface::ENTRY_TYPE_PROFILE_STATUS_CHANGED);
            }

            $this->entityManager->save($profile, ['coreSubscriptionToUpdate' => $subscription]);
            $this->profileRegistry->push($profile);

            return $result;
        }

        return null;
    }
}
