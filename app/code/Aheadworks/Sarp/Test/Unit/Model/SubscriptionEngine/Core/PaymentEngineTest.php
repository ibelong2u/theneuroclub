<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Core;

use Aheadworks\Sarp\Model\SubscriptionEngine\Core\PaymentEngine;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionResult;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\PaymentProcessor;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\Repository as PaymentRepository;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Scheduler;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\PayableChecker;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Repository as SubscriptionRepository;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Core\PaymentEngine
 */
class PaymentEngineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentEngine
     */
    private $engine;

    /**
     * @var Scheduler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $schedulerMock;

    /**
     * @var PaymentProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentProcessorMock;

    /**
     * @var PayableChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payableCheckerMock;

    /**
     * @var SubscriptionRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionRepoMock;

    /**
     * @var PaymentRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentRepoMock;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->schedulerMock = $this->createMock(Scheduler::class);
        $this->paymentProcessorMock = $this->createMock(PaymentProcessor::class);
        $this->payableCheckerMock = $this->createMock(PayableChecker::class);
        $this->subscriptionRepoMock = $this->createMock(SubscriptionRepository::class);
        $this->paymentRepoMock = $this->createMock(PaymentRepository::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->engine = $objectManager->getObject(
            PaymentEngine::class,
            [
                'scheduler' => $this->schedulerMock,
                'paymentProcessor' => $this->paymentProcessorMock,
                'payableChecker' => $this->payableCheckerMock,
                'subscriptionRepo' => $this->subscriptionRepoMock,
                'paymentRepo' => $this->paymentRepoMock,
                'dateTime' => $this->dateTimeMock
            ]
        );
    }

    public function testPayInitial()
    {
        $profileId = 1;
        $paymentType = 'initial';

        $subscriptionMock = $this->createMock(Subscription::class);
        $paymentMock = $this->createMock(Payment::class);
        $actionResultMock = $this->createMock(ActionResult::class);

        $this->subscriptionRepoMock->expects($this->once())
            ->method('getByProfileId')
            ->with($profileId)
            ->willReturn($subscriptionMock);
        $this->schedulerMock->expects($this->once())
            ->method('scheduleInitial')
            ->with($subscriptionMock)
            ->willReturn([$paymentMock]);
        $this->paymentProcessorMock->expects($this->once())
            ->method('pay')
            ->with($paymentMock, true)
            ->willReturn($actionResultMock);
        $paymentMock->expects($this->once())
            ->method('__call')
            ->with('getType')
            ->willReturn($paymentType);
        $this->schedulerMock->expects($this->once())
            ->method('scheduleNext')
            ->with($subscriptionMock);

        $this->assertEquals([$paymentType => $actionResultMock], $this->engine->payInitial($profileId));
    }

    public function testPayScheduledForToday()
    {
        $today = '2017-08-01';
        $scheduledAt = '2017-08-01';
        $subscriptionId = 1;

        $paymentMock = $this->createMock(Payment::class);
        $subscriptionMock = $this->createMock(Subscription::class);

        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true, false)
            ->willReturn($today);
        $this->paymentRepoMock->expects($this->once())
            ->method('getListOfPendingForToday')
            ->willReturn([$paymentMock]);
        $this->subscriptionRepoMock->expects($this->once())
            ->method('get')
            ->with($subscriptionId)
            ->willReturn($subscriptionMock);
        $paymentMock->expects($this->exactly(3))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getScheduledAt', [], $scheduledAt],
                    ['getSubscriptionId', [], $subscriptionId]
                ]
            );
        $this->schedulerMock->expects($this->never())
            ->method('reschedule')
            ->with($subscriptionMock, $paymentMock);
        $this->paymentProcessorMock->expects($this->once())
            ->method('pay')
            ->with($paymentMock);
        $this->schedulerMock->expects($this->once())
            ->method('scheduleNext')
            ->with($subscriptionMock);

        $this->engine->payScheduledForToday();
    }

    public function testPayScheduledForTodayRescheduledToToday()
    {
        $today = '2017-08-02';
        $scheduledAt = '2017-08-01';
        $subscriptionId = 1;

        $paymentMock = $this->createMock(Payment::class);
        $subscriptionMock = $this->createMock(Subscription::class);

        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true, false)
            ->willReturn($today);
        $this->paymentRepoMock->expects($this->once())
            ->method('getListOfPendingForToday')
            ->willReturn([$paymentMock]);
        $this->subscriptionRepoMock->expects($this->once())
            ->method('get')
            ->with($subscriptionId)
            ->willReturn($subscriptionMock);
        $paymentMock->expects($this->exactly(3))
            ->method('__call')
            ->withConsecutive(
                ['getSubscriptionId'],
                ['getScheduledAt'],
                ['getScheduledAt']
            )
            ->willReturnOnConsecutiveCalls(
                $subscriptionId,
                $scheduledAt,
                $today
            );
        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->with($subscriptionMock)
            ->willReturn(true);
        $this->schedulerMock->expects($this->once())
            ->method('reschedule')
            ->with($subscriptionMock, $paymentMock);
        $this->paymentProcessorMock->expects($this->once())
            ->method('pay')
            ->with($paymentMock);
        $this->schedulerMock->expects($this->once())
            ->method('scheduleNext')
            ->with($subscriptionMock);

        $this->engine->payScheduledForToday();
    }

    public function testPayScheduledForTodayRescheduledToTomorrow()
    {
        $today = '2017-08-02';
        $tomorrow = '2017-08-03';
        $scheduledAt = '2017-08-01';
        $subscriptionId = 1;

        $paymentMock = $this->createMock(Payment::class);
        $subscriptionMock = $this->createMock(Subscription::class);

        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true, false)
            ->willReturn($today);
        $this->paymentRepoMock->expects($this->once())
            ->method('getListOfPendingForToday')
            ->willReturn([$paymentMock]);
        $this->subscriptionRepoMock->expects($this->once())
            ->method('get')
            ->with($subscriptionId)
            ->willReturn($subscriptionMock);
        $paymentMock->expects($this->exactly(3))
            ->method('__call')
            ->withConsecutive(
                ['getSubscriptionId'],
                ['getScheduledAt'],
                ['getScheduledAt']
            )
            ->willReturnOnConsecutiveCalls(
                $subscriptionId,
                $scheduledAt,
                $tomorrow
            );
        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->with($subscriptionMock)
            ->willReturn(true);
        $this->schedulerMock->expects($this->once())
            ->method('reschedule')
            ->with($subscriptionMock, $paymentMock);
        $this->paymentProcessorMock->expects($this->never())
            ->method('pay')
            ->with($paymentMock);
        $this->schedulerMock->expects($this->never())
            ->method('scheduleNext')
            ->with($subscriptionMock);

        $this->engine->payScheduledForToday();
    }

    public function testPayScheduledForTodayNotPayable()
    {
        $today = '2017-08-02';
        $scheduledAt = '2017-08-01';
        $subscriptionId = 1;

        $paymentMock = $this->createMock(Payment::class);
        $subscriptionMock = $this->createMock(Subscription::class);

        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true, false)
            ->willReturn($today);
        $this->paymentRepoMock->expects($this->once())
            ->method('getListOfPendingForToday')
            ->willReturn([$paymentMock]);
        $this->subscriptionRepoMock->expects($this->once())
            ->method('get')
            ->with($subscriptionId)
            ->willReturn($subscriptionMock);
        $paymentMock->expects($this->exactly(3))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getScheduledAt', [], $scheduledAt],
                    ['getSubscriptionId', [], $subscriptionId]
                ]
            );
        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->with($subscriptionMock)
            ->willReturn(false);
        $this->schedulerMock->expects($this->never())
            ->method('reschedule')
            ->with($subscriptionMock, $paymentMock);
        $this->paymentProcessorMock->expects($this->never())
            ->method('pay')
            ->with($paymentMock);
        $this->schedulerMock->expects($this->never())
            ->method('scheduleNext')
            ->with($subscriptionMock);

        $this->engine->payScheduledForToday();
    }

    public function testPayReattemptsForToday()
    {
        $subscriptionId = 1;
        $scheduledAt = '2017-08-01';

        $paymentMock = $this->createMock(Payment::class);
        $subscriptionMock = $this->createMock(Subscription::class);

        $this->paymentRepoMock->expects($this->once())
            ->method('getListOfRetryingForToday')
            ->willReturn([$paymentMock]);
        $this->paymentRepoMock->expects($this->once())
            ->method('getListOfPendingForToday')
            ->willReturn([]);
        $this->paymentProcessorMock->expects($this->once())
            ->method('pay')
            ->with($paymentMock, false, true);
        $paymentMock->expects($this->exactly(2))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getSubscriptionId', [], $subscriptionId],
                    ['getScheduledAt', [], $scheduledAt]
                ]
            );
        $this->subscriptionRepoMock->expects($this->once())
            ->method('get')
            ->with($subscriptionId)
            ->willReturn($subscriptionMock);
        $this->schedulerMock->expects($this->once())
            ->method('scheduleNext')
            ->with($subscriptionMock, $scheduledAt);

        $this->engine->payReattemptsForToday();
    }
}
