<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Core;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\Logger\LoggerInterface;
use Aheadworks\Sarp\Model\Profile\Source\Status;
use Aheadworks\Sarp\Model\ProfileRegistry;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Exception\PaymentActionException;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionPool;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionResult;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\PaymentInfoBuilder;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\Repository as PaymentRepository;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\PaymentProcessor;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Scheduler;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\ExpirationChecker;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\PayableChecker;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Repository as SubscriptionRepository;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\SecureDataFilter;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Core\PaymentProcessor
 */
class PaymentProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentProcessor
     */
    private $processor;

    /**
     * @var Scheduler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $schedulerMock;

    /**
     * @var SubscriptionRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionRepoMock;

    /**
     * @var PaymentRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentRepoMock;

    /**
     * @var ProfileRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileRepositoryMock;

    /**
     * @var ProfileRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileRegistryMock;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var ActionPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentActionPoolMock;

    /**
     * @var PaymentInfoBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentInfoBuilderMock;

    /**
     * @var PayableChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payableCheckerMock;

    /**
     * @var ExpirationChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $expirationCheckerMock;

    /**
     * @var SecureDataFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $secureDataFilterMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->schedulerMock = $this->createMock(Scheduler::class);
        $this->subscriptionRepoMock = $this->createMock(SubscriptionRepository::class);
        $this->paymentRepoMock = $this->createMock(PaymentRepository::class);
        $this->profileRepositoryMock = $this->getMockForAbstractClass(ProfileRepositoryInterface::class);
        $this->profileRegistryMock = $this->createMock(ProfileRegistry::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->paymentActionPoolMock = $this->createMock(ActionPool::class);
        $this->paymentInfoBuilderMock = $this->createMock(PaymentInfoBuilder::class);
        $this->payableCheckerMock = $this->createMock(PayableChecker::class);
        $this->expirationCheckerMock = $this->createMock(ExpirationChecker::class);
        $this->secureDataFilterMock = $this->createMock(SecureDataFilter::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->processor = $objectManager->getObject(
            PaymentProcessor::class,
            [
                'scheduler' => $this->schedulerMock,
                'subscriptionRepo' => $this->subscriptionRepoMock,
                'paymentRepo' => $this->paymentRepoMock,
                'profileRepository' => $this->profileRepositoryMock,
                'profileRegistry' => $this->profileRegistryMock,
                'entityManager' => $this->entityManagerMock,
                'paymentActionPool' => $this->paymentActionPoolMock,
                'paymentInfoBuilder' => $this->paymentInfoBuilderMock,
                'payableChecker' => $this->payableCheckerMock,
                'expirationChecker' => $this->expirationCheckerMock,
                'secureDataFilter' => $this->secureDataFilterMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testPayInitial()
    {
        $subscriptionId = 1;
        $paymentType = 'initial';
        $paymentStatus = 'paid';
        $orderId = 2;
        $profileId = 3;
        $paymentData = ['field' => 'value'];
        $paymentDataFiltered = ['field' => 'value_filtered'];
        $engineCode = 'engine_code';
        $methodCode = 'method_code';

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $subscriptionMock = $this->createMock(Subscription::class);
        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        $paymentInfoMock = $this->getMockForAbstractClass(ProfilePaymentInfoInterface::class);
        $actionMock = $this->getMockForAbstractClass(ActionInterface::class);
        $actionResultMock = $this->createMock(ActionResult::class);
        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);

        $paymentMock->expects($this->exactly(5))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getSubscriptionId', [], $subscriptionId],
                    ['getType', [], $paymentType],
                    ['setStatus', [$paymentStatus], $this->returnSelf()],
                    ['setOrderId', [$orderId], $this->returnSelf()]
                ]
            );
        $this->subscriptionRepoMock->expects($this->once())
            ->method('get')
            ->with($subscriptionId)
            ->willReturn($subscriptionMock);
        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->with($subscriptionMock, false)
            ->willReturn(true);
        $subscriptionMock->expects($this->exactly(5))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getProfileId', [], $profileId],
                    ['getPaymentData', [], $paymentData],
                    ['setIsInitialPaid', [true], $this->returnSelf()],
                    ['setPaymentData', [$paymentDataFiltered], $this->returnSelf()]
                ]
            );
        $this->profileRepositoryMock->expects($this->once())
            ->method('get')
            ->with($profileId)
            ->willReturn($profileMock);
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('setProfile')
            ->with($profileMock)
            ->willReturnSelf();
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('setPaymentType')
            ->with($paymentType)
            ->willReturnSelf();
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($paymentInfoMock);
        $profileMock->expects($this->once())
            ->method('getEngineCode')
            ->willReturn($engineCode);
        $profileMock->expects($this->exactly(2))
            ->method('getPaymentMethodCode')
            ->willReturn($methodCode);
        $this->paymentActionPoolMock->expects($this->once())
            ->method('getAction')
            ->with($engineCode, $methodCode)
            ->willReturn($actionMock);
        $actionMock->expects($this->once())->method('pay')
            ->with($profileMock, $paymentInfoMock, $paymentData)
            ->willReturn($actionResultMock);
        $actionResultMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($paymentStatus);
        $actionResultMock->expects($this->exactly(2))
            ->method('getOrder')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($orderId);
        $this->paymentRepoMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);
        $profileMock->expects($this->once())
            ->method('setStatus')
            ->with(Status::ACTIVE);
        $this->secureDataFilterMock->expects($this->once())
            ->method('filter')
            ->with($paymentData, $methodCode)
            ->willReturn($paymentDataFiltered);
        $this->expirationCheckerMock->expects($this->once())
            ->method('isExpire')
            ->with($subscriptionMock, $profileMock)
            ->willReturn(false);
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($profileMock, ['coreSubscriptionToUpdate' => $subscriptionMock]);
        $this->profileRegistryMock->expects($this->once())
            ->method('push')
            ->with($profileMock);

        $this->assertSame($actionResultMock, $this->processor->pay($paymentMock, true));
    }

    public function testPayTrial()
    {
        $subscriptionId = 1;
        $paymentType = 'trial';
        $paymentStatus = 'paid';
        $trialPaymentsCount = 1;
        $orderId = 2;
        $profileId = 3;
        $paymentData = ['field' => 'value'];
        $paymentDataFiltered = ['field' => 'value_filtered'];
        $engineCode = 'engine_code';
        $methodCode = 'method_code';

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $subscriptionMock = $this->createMock(Subscription::class);
        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        $paymentInfoMock = $this->getMockForAbstractClass(ProfilePaymentInfoInterface::class);
        $actionMock = $this->getMockForAbstractClass(ActionInterface::class);
        $actionResultMock = $this->createMock(ActionResult::class);
        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);

        $paymentMock->expects($this->exactly(6))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getSubscriptionId', [], $subscriptionId],
                    ['getType', [], $paymentType],
                    ['setStatus', [$paymentStatus], $this->returnSelf()],
                    ['setOrderId', [$orderId], $this->returnSelf()]
                ]
            );

        $this->subscriptionRepoMock->expects($this->once())
            ->method('get')
            ->with($subscriptionId)
            ->willReturn($subscriptionMock);
        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->with($subscriptionMock, false)
            ->willReturn(true);
        $subscriptionMock->expects($this->exactly(6))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getProfileId', [], $profileId],
                    ['getPaymentData', [], $paymentData],
                    ['getTrialPaymentsCount', [], $trialPaymentsCount],
                    ['setPaymentData', [$paymentDataFiltered], $this->returnSelf()],
                    ['setTrialPaymentsCount', [$trialPaymentsCount + 1], $this->returnSelf()]
                ]
            );
        $this->profileRepositoryMock->expects($this->once())
            ->method('get')
            ->with($profileId)
            ->willReturn($profileMock);
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('setProfile')
            ->with($profileMock)
            ->willReturnSelf();
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('setPaymentType')
            ->with($paymentType)
            ->willReturnSelf();
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($paymentInfoMock);
        $profileMock->expects($this->once())
            ->method('getEngineCode')
            ->willReturn($engineCode);
        $profileMock->expects($this->exactly(2))
            ->method('getPaymentMethodCode')
            ->willReturn($methodCode);
        $this->paymentActionPoolMock->expects($this->once())
            ->method('getAction')
            ->with($engineCode, $methodCode)
            ->willReturn($actionMock);
        $this->loggerMock->expects($this->exactly(2))
            ->method('notice')
            ->withConsecutive(
                [$profileMock, LoggerInterface::ENTRY_TYPE_PAYMENT_STARTED, []],
                [$profileMock, LoggerInterface::ENTRY_TYPE_PAYMENT_AUTHORIZED, ['order' => $orderMock]]
            );
        $actionMock->expects($this->once())->method('pay')
            ->with($profileMock, $paymentInfoMock, $paymentData)
            ->willReturn($actionResultMock);
        $actionResultMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($paymentStatus);
        $actionResultMock->expects($this->exactly(3))
            ->method('getOrder')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($orderId);
        $this->paymentRepoMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);
        $this->secureDataFilterMock->expects($this->once())
            ->method('filter')
            ->with($paymentData, $methodCode)
            ->willReturn($paymentDataFiltered);
        $this->expirationCheckerMock->expects($this->once())
            ->method('isExpire')
            ->with($subscriptionMock, $profileMock)
            ->willReturn(false);
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($profileMock, ['coreSubscriptionToUpdate' => $subscriptionMock]);
        $this->profileRegistryMock->expects($this->once())
            ->method('push')
            ->with($profileMock);

        $this->assertSame($actionResultMock, $this->processor->pay($paymentMock));
    }

    public function testPayRegular()
    {
        $subscriptionId = 1;
        $paymentType = 'regular';
        $paymentStatus = 'paid';
        $regularPaymentsCount = 1;
        $orderId = 2;
        $profileId = 3;
        $paymentData = ['field' => 'value'];
        $paymentDataFiltered = ['field' => 'value_filtered'];
        $engineCode = 'engine_code';
        $methodCode = 'method_code';

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $subscriptionMock = $this->createMock(Subscription::class);
        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        $paymentInfoMock = $this->getMockForAbstractClass(ProfilePaymentInfoInterface::class);
        $actionMock = $this->getMockForAbstractClass(ActionInterface::class);
        $actionResultMock = $this->createMock(ActionResult::class);
        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);

        $paymentMock->expects($this->exactly(6))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getSubscriptionId', [], $subscriptionId],
                    ['getType', [], $paymentType],
                    ['setStatus', [$paymentStatus], $this->returnSelf()],
                    ['setOrderId', [$orderId], $this->returnSelf()]
                ]
            );

        $this->subscriptionRepoMock->expects($this->once())
            ->method('get')
            ->with($subscriptionId)
            ->willReturn($subscriptionMock);
        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->with($subscriptionMock, false)
            ->willReturn(true);
        $subscriptionMock->expects($this->exactly(6))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getProfileId', [], $profileId],
                    ['getPaymentData', [], $paymentData],
                    ['getRegularPaymentsCount', [], $regularPaymentsCount],
                    ['setPaymentData', [$paymentDataFiltered], $this->returnSelf()],
                    ['setRegularPaymentsCount', [$regularPaymentsCount + 1], $this->returnSelf()]
                ]
            );
        $this->profileRepositoryMock->expects($this->once())
            ->method('get')
            ->with($profileId)
            ->willReturn($profileMock);
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('setProfile')
            ->with($profileMock)
            ->willReturnSelf();
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('setPaymentType')
            ->with($paymentType)
            ->willReturnSelf();
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($paymentInfoMock);
        $profileMock->expects($this->once())
            ->method('getEngineCode')
            ->willReturn($engineCode);
        $profileMock->expects($this->exactly(2))
            ->method('getPaymentMethodCode')
            ->willReturn($methodCode);
        $this->paymentActionPoolMock->expects($this->once())
            ->method('getAction')
            ->with($engineCode, $methodCode)
            ->willReturn($actionMock);
        $this->loggerMock->expects($this->exactly(2))
            ->method('notice')
            ->withConsecutive(
                [$profileMock, LoggerInterface::ENTRY_TYPE_PAYMENT_STARTED, []],
                [$profileMock, LoggerInterface::ENTRY_TYPE_PAYMENT_AUTHORIZED, ['order' => $orderMock]]
            );
        $actionMock->expects($this->once())->method('pay')
            ->with($profileMock, $paymentInfoMock, $paymentData)
            ->willReturn($actionResultMock);
        $actionResultMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($paymentStatus);
        $actionResultMock->expects($this->exactly(3))
            ->method('getOrder')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($orderId);
        $this->paymentRepoMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);
        $this->secureDataFilterMock->expects($this->once())
            ->method('filter')
            ->with($paymentData, $methodCode)
            ->willReturn($paymentDataFiltered);
        $this->expirationCheckerMock->expects($this->once())
            ->method('isExpire')
            ->with($subscriptionMock, $profileMock)
            ->willReturn(false);
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($profileMock, ['coreSubscriptionToUpdate' => $subscriptionMock]);
        $this->profileRegistryMock->expects($this->once())
            ->method('push')
            ->with($profileMock);

        $this->assertSame($actionResultMock, $this->processor->pay($paymentMock));
    }

    public function testPayAndExpire()
    {
        $subscriptionId = 1;
        $paymentType = 'regular';
        $paymentStatus = 'paid';
        $regularPaymentsCount = 1;
        $orderId = 2;
        $profileId = 3;
        $paymentData = ['field' => 'value'];
        $paymentDataFiltered = ['field' => 'value_filtered'];
        $engineCode = 'engine_code';
        $methodCode = 'method_code';

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $subscriptionMock = $this->createMock(Subscription::class);
        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        $paymentInfoMock = $this->getMockForAbstractClass(ProfilePaymentInfoInterface::class);
        $actionMock = $this->getMockForAbstractClass(ActionInterface::class);
        $actionResultMock = $this->createMock(ActionResult::class);
        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);

        $paymentMock->expects($this->exactly(6))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getSubscriptionId', [], $subscriptionId],
                    ['getType', [], $paymentType],
                    ['setStatus', [$paymentStatus], $this->returnSelf()],
                    ['setOrderId', [$orderId], $this->returnSelf()]
                ]
            );

        $this->subscriptionRepoMock->expects($this->once())
            ->method('get')
            ->with($subscriptionId)
            ->willReturn($subscriptionMock);
        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->with($subscriptionMock, false)
            ->willReturn(true);
        $subscriptionMock->expects($this->exactly(6))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getProfileId', [], $profileId],
                    ['getPaymentData', [], $paymentData],
                    ['getRegularPaymentsCount', [], $regularPaymentsCount],
                    ['setPaymentData', [$paymentDataFiltered], $this->returnSelf()],
                    ['setRegularPaymentsCount', [$regularPaymentsCount + 1], $this->returnSelf()]
                ]
            );
        $this->profileRepositoryMock->expects($this->once())
            ->method('get')
            ->with($profileId)
            ->willReturn($profileMock);
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('setProfile')
            ->with($profileMock)
            ->willReturnSelf();
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('setPaymentType')
            ->with($paymentType)
            ->willReturnSelf();
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($paymentInfoMock);
        $profileMock->expects($this->once())
            ->method('getEngineCode')
            ->willReturn($engineCode);
        $profileMock->expects($this->exactly(2))
            ->method('getPaymentMethodCode')
            ->willReturn($methodCode);
        $this->paymentActionPoolMock->expects($this->once())
            ->method('getAction')
            ->with($engineCode, $methodCode)
            ->willReturn($actionMock);
        $this->loggerMock->expects($this->exactly(3))
            ->method('notice')
            ->withConsecutive(
                [$profileMock, LoggerInterface::ENTRY_TYPE_PAYMENT_STARTED, []],
                [$profileMock, LoggerInterface::ENTRY_TYPE_PAYMENT_AUTHORIZED, ['order' => $orderMock]],
                [$profileMock, LoggerInterface::ENTRY_TYPE_PROFILE_STATUS_CHANGED]
            );
        $actionMock->expects($this->once())->method('pay')
            ->with($profileMock, $paymentInfoMock, $paymentData)
            ->willReturn($actionResultMock);
        $actionResultMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($paymentStatus);
        $actionResultMock->expects($this->exactly(3))
            ->method('getOrder')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($orderId);
        $this->paymentRepoMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);
        $this->secureDataFilterMock->expects($this->once())
            ->method('filter')
            ->with($paymentData, $methodCode)
            ->willReturn($paymentDataFiltered);
        $this->expirationCheckerMock->expects($this->once())
            ->method('isExpire')
            ->with($subscriptionMock, $profileMock)
            ->willReturn(true);
        $profileMock->expects($this->once())
            ->method('setStatus')
            ->with(Status::EXPIRED);
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($profileMock, ['coreSubscriptionToUpdate' => $subscriptionMock]);
        $this->profileRegistryMock->expects($this->once())
            ->method('push')
            ->with($profileMock);

        $this->assertSame($actionResultMock, $this->processor->pay($paymentMock));
    }

    public function testPaySuccessReattempt()
    {
        $subscriptionId = 1;
        $paymentType = 'regular';
        $paymentStatus = 'paid';
        $regularPaymentsCount = 1;
        $orderId = 2;
        $profileId = 3;
        $paymentData = ['field' => 'value'];
        $paymentDataFiltered = ['field' => 'value_filtered'];
        $engineCode = 'engine_code';
        $methodCode = 'method_code';

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $subscriptionMock = $this->createMock(Subscription::class);
        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        $paymentInfoMock = $this->getMockForAbstractClass(ProfilePaymentInfoInterface::class);
        $actionMock = $this->getMockForAbstractClass(ActionInterface::class);
        $actionResultMock = $this->createMock(ActionResult::class);
        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);

        $paymentMock->expects($this->exactly(6))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getSubscriptionId', [], $subscriptionId],
                    ['getType', [], $paymentType],
                    ['setStatus', [$paymentStatus], $this->returnSelf()],
                    ['setOrderId', [$orderId], $this->returnSelf()]
                ]
            );

        $this->subscriptionRepoMock->expects($this->once())
            ->method('get')
            ->with($subscriptionId)
            ->willReturn($subscriptionMock);
        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->with($subscriptionMock, true)
            ->willReturn(true);
        $subscriptionMock->expects($this->exactly(7))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getProfileId', [], $profileId],
                    ['getPaymentData', [], $paymentData],
                    ['getRegularPaymentsCount', [], $regularPaymentsCount],
                    ['setPaymentData', [$paymentDataFiltered], $this->returnSelf()],
                    ['setRegularPaymentsCount', [$regularPaymentsCount + 1], $this->returnSelf()],
                    ['setPaymentFailuresCount', [0], $this->returnSelf()]
                ]
            );
        $this->profileRepositoryMock->expects($this->once())
            ->method('get')
            ->with($profileId)
            ->willReturn($profileMock);
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('setProfile')
            ->with($profileMock)
            ->willReturnSelf();
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('setPaymentType')
            ->with($paymentType)
            ->willReturnSelf();
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($paymentInfoMock);
        $profileMock->expects($this->once())
            ->method('getEngineCode')
            ->willReturn($engineCode);
        $profileMock->expects($this->exactly(2))
            ->method('getPaymentMethodCode')
            ->willReturn($methodCode);
        $this->paymentActionPoolMock->expects($this->once())
            ->method('getAction')
            ->with($engineCode, $methodCode)
            ->willReturn($actionMock);
        $this->loggerMock->expects($this->exactly(2))
            ->method('notice')
            ->withConsecutive(
                [$profileMock, LoggerInterface::ENTRY_TYPE_PAYMENT_STARTED, []],
                [$profileMock, LoggerInterface::ENTRY_TYPE_PAYMENT_AUTHORIZED, ['order' => $orderMock]]
            );
        $actionMock->expects($this->once())->method('pay')
            ->with($profileMock, $paymentInfoMock, $paymentData)
            ->willReturn($actionResultMock);
        $actionResultMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($paymentStatus);
        $actionResultMock->expects($this->exactly(3))
            ->method('getOrder')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($orderId);
        $this->paymentRepoMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);
        $profileMock->expects($this->once())
            ->method('setStatus')
            ->with(Status::ACTIVE);
        $this->secureDataFilterMock->expects($this->once())
            ->method('filter')
            ->with($paymentData, $methodCode)
            ->willReturn($paymentDataFiltered);
        $this->expirationCheckerMock->expects($this->once())
            ->method('isExpire')
            ->with($subscriptionMock, $profileMock)
            ->willReturn(false);
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($profileMock, ['coreSubscriptionToUpdate' => $subscriptionMock]);
        $this->profileRegistryMock->expects($this->once())
            ->method('push')
            ->with($profileMock);

        $this->assertSame($actionResultMock, $this->processor->pay($paymentMock, false, true));
    }

    /**
     * @expectedException \Aheadworks\Sarp\Model\SubscriptionEngine\Core\Exception\PaymentException
     * @expectedExceptionMessage Payment has been failed: Error message
     */
    public function testPayFailed()
    {
        $subscriptionId = 1;
        $profileId = 2;
        $paymentType = 'regular';
        $exceptionMessage = 'Error message';
        $paymentData = ['field' => 'value'];
        $engineCode = 'engine_code';
        $methodCode = 'method_code';
        $failuresCount = 0;

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $reattemptPaymentMock = $this->createMock(Payment::class);
        $subscriptionMock = $this->createMock(Subscription::class);
        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        $paymentInfoMock = $this->getMockForAbstractClass(ProfilePaymentInfoInterface::class);
        $actionMock = $this->getMockForAbstractClass(ActionInterface::class);
        $exception = new PaymentActionException(__($exceptionMessage));

        $paymentMock->expects($this->exactly(3))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getSubscriptionId', [], $subscriptionId],
                    ['getType', [], $paymentType],
                    ['setStatus', ['pending'], $this->returnSelf()]
                ]
            );
        $this->subscriptionRepoMock->expects($this->once())
            ->method('get')
            ->with($subscriptionId)
            ->willReturn($subscriptionMock);
        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->with($subscriptionMock, false)
            ->willReturn(true);
        $subscriptionMock->expects($this->exactly(4))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getProfileId', [], $profileId],
                    ['getPaymentData', [], $paymentData],
                    ['getPaymentFailuresCount', [], $failuresCount],
                    ['setPaymentFailuresCount', [$failuresCount + 1], $this->returnSelf()]
                ]
            );
        $this->profileRepositoryMock->expects($this->once())
            ->method('get')
            ->with($profileId)
            ->willReturn($profileMock);
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('setProfile')
            ->with($profileMock)
            ->willReturnSelf();
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('setPaymentType')
            ->with($paymentType)
            ->willReturnSelf();
        $this->paymentInfoBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($paymentInfoMock);
        $profileMock->expects($this->once())
            ->method('getEngineCode')
            ->willReturn($engineCode);
        $profileMock->expects($this->once())
            ->method('getPaymentMethodCode')
            ->willReturn($methodCode);
        $this->paymentActionPoolMock->expects($this->once())
            ->method('getAction')
            ->with($engineCode, $methodCode)
            ->willReturn($actionMock);
        $this->loggerMock->expects($this->once())
            ->method('notice')
            ->with($profileMock, LoggerInterface::ENTRY_TYPE_PAYMENT_STARTED);
        $actionMock->expects($this->once())->method('pay')
            ->with($profileMock, $paymentInfoMock, $paymentData)
            ->willThrowException($exception);
        $this->schedulerMock->expects($this->once())
            ->method('scheduleReattempt')
            ->with($subscriptionMock, $paymentMock)
            ->willReturn([$reattemptPaymentMock]);
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($profileMock, ['coreSubscriptionToUpdate' => $subscriptionMock]);
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                $profileMock,
                LoggerInterface::ENTRY_TYPE_PAYMENT_FAIL,
                [
                    'exception' => $exception,
                    'reattempts' => [$reattemptPaymentMock]
                ]
            );

        $this->processor->pay($paymentMock);
    }
}
