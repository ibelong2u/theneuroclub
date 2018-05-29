<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\Logger\LoggerInterface;
use Aheadworks\Sarp\Model\Profile\Source\Status;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Exception\CouldNotCreateSubscriptionException;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Exception\PaymentException;
use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Subscription as SubscriptionResource;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Generator\ReferenceId as ReferenceIdGenerator;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Status\Management as StatusManagement;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class SubscriptionEngine
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriptionEngine
{
    /**
     * @var PaymentEngine
     */
    private $paymentEngine;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ReferenceIdGenerator
     */
    private $referenceIdGenerator;

    /**
     * @var StatusManagement
     */
    private $statusManagement;

    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param PaymentEngine $paymentEngine
     * @param EntityManager $entityManager
     * @param ReferenceIdGenerator $referenceIdGenerator
     * @param StatusManagement $statusManagement
     * @param SubscriptionFactory $subscriptionFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param DataObjectProcessor $dataObjectProcessor
     * @param LoggerInterface $logger
     */
    public function __construct(
        PaymentEngine $paymentEngine,
        EntityManager $entityManager,
        ReferenceIdGenerator $referenceIdGenerator,
        StatusManagement $statusManagement,
        SubscriptionFactory $subscriptionFactory,
        ProfileRepositoryInterface $profileRepository,
        DataObjectProcessor $dataObjectProcessor,
        LoggerInterface $logger
    ) {
        $this->paymentEngine = $paymentEngine;
        $this->entityManager = $entityManager;
        $this->referenceIdGenerator = $referenceIdGenerator;
        $this->statusManagement = $statusManagement;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->profileRepository = $profileRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->logger = $logger;
    }

    /**
     * Create subscription
     *
     * @param ProfileInterface $profile
     * @param SubscriptionsCartPaymentInterface $payment
     * @return ProfileInterface
     * @throws CouldNotCreateSubscriptionException
     * @throws PaymentException
     */
    public function createSubscription(ProfileInterface $profile, SubscriptionsCartPaymentInterface $payment)
    {
        if ($profile->getInitialFee() == 0) {
            $profile->setIsInitialFeeEnabled(false);
        }
        if ($profile->getTrialSubtotal() == 0) {
            $profile->setIsTrialPeriodEnabled(false);
        }

        $status = $profile->getIsInitialFeeEnabled() ? Status::PENDING : Status::ACTIVE;
        $profile
            ->setReferenceId($this->referenceIdGenerator->getReferenceId($profile))
            ->setStatus($status);

        /** @var Subscription $subscription */
        $subscription = $this->subscriptionFactory->create();
        $subscription->setPaymentData($payment->getPaymentData());

        try {
            $this->entityManager->save($profile, ['coreSubscriptionToAssign' => $subscription]);
            $paymentResults = $this->paymentEngine->payInitial($profile->getProfileId());
            $this->logger->notice(
                $profile,
                LoggerInterface::ENTRY_TYPE_PROFILE_CREATED_SUCCESSFUL,
                ['paymentResults' => $paymentResults]
            );
        } catch (\Exception $exception) {
            if ($profile->getProfileId()) {
                $this->entityManager->delete($profile);
            }
            throw new CouldNotCreateSubscriptionException(__($exception->getMessage()));
        }

        return $this->profileRepository->get($profile->getProfileId());
    }

    /**
     * Update subscription
     *
     * @param ProfileInterface $profile
     * @return ProfileInterface
     */
    public function updateSubscription(ProfileInterface $profile)
    {
        // By the current feature lists no implementation
        return $profile;
    }

    /**
     * Core stub of retrieve subscription data logic
     *
     * @param string $referenceId
     * @return array
     * @codeCoverageIgnore
     */
    public function getSubscriptionData($referenceId)
    {
        $profile = $this->profileRepository->getByReferenceId($referenceId);
        return $this->dataObjectProcessor->buildOutputDataArray($profile, ProfileInterface::class);
    }

    /**
     * todo: cancel all payments on subscription cancellation
     * Change status
     *
     * @param string $referenceId
     * @param string $action
     * @return string|null
     * @throws \Exception
     */
    public function changeStatus($referenceId, $action)
    {
        $profile = $this->profileRepository->getByReferenceId($referenceId);
        return $this->statusManagement->changeStatus($profile, $action);
    }
}
