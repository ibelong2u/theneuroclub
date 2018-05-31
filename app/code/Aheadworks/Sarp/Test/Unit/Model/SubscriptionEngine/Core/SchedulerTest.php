<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Core;

use Aheadworks\Sarp\Model\Profile\PaymentInfo;
use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment as CorePaymentResource;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\DataResolver\NextPaymentDate;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\DataResolver\NextReattemptDate;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\PaymentFactory;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\Repository;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\PayableChecker;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Repository as SubscriptionRepository;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Scheduler;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Scheduler\PaymentTypesEstimation;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Core\Scheduler
 */
class SchedulerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var PaymentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentFactoryMock;

    /**
     * @var Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentRepoMock;

    /**
     * @var SubscriptionRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionRepoMock;

    /**
     * @var PaymentTypesEstimation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTypeEstimationMock;

    /**
     * @var PayableChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payableCheckerMock;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var CoreDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreDateMock;

    /**
     * @var NextPaymentDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nextPaymentDateMock;

    /**
     * @var NextReattemptDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nextReattemptDateMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->paymentFactoryMock = $this->createMock(PaymentFactory::class);
        $this->paymentRepoMock = $this->createMock(Repository::class);
        $this->subscriptionRepoMock = $this->createMock(SubscriptionRepository::class);
        $this->paymentTypeEstimationMock = $this->createMock(PaymentTypesEstimation::class);
        $this->payableCheckerMock = $this->createMock(PayableChecker::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->coreDateMock = $this->createMock(CoreDate::class);
        $this->nextPaymentDateMock = $this->createMock(NextPaymentDate::class);
        $this->nextReattemptDateMock = $this->createMock(NextReattemptDate::class);
        $this->scheduler = $objectManager->getObject(
            Scheduler::class,
            [
                'paymentFactory' => $this->paymentFactoryMock,
                'paymentRepo' => $this->paymentRepoMock,
                'subscriptionRepo' => $this->subscriptionRepoMock,
                'paymentTypeEstimation' => $this->paymentTypeEstimationMock,
                'payableChecker' => $this->payableCheckerMock,
                'dateTime' => $this->dateTimeMock,
                'coreDate' => $this->coreDateMock,
                'nextPaymentDate' => $this->nextPaymentDateMock,
                'nextReattemptDate' => $this->nextReattemptDateMock
            ]
        );
    }

    public function testScheduleInitialHasPayments()
    {
        $subscriptionId = 1;
        $now = '2017-08-01 15:03:00';
        $startDate = '2017-08-01';
        $initialDate = '2017-08-01';

        /** @var Subscription|\PHPUnit_Framework_MockObject_MockObject $subscriptionMock */
        $subscriptionMock = $this->createMock(Subscription::class);
        $paymentMock = $this->createMock(Payment::class);

        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->with($subscriptionMock)
            ->willReturn(true);
        $subscriptionMock->expects($this->exactly(3))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getSubscriptionId', [], $subscriptionId],
                    ['getStartDate', [], $startDate]
                ]
            );
        $this->paymentRepoMock->expects($this->once())
            ->method('has')
            ->with(
                [
                    ['status', Payment::STATUS_PENDING],
                    ['subscription_id', $subscriptionId]
                ]
            )
            ->willReturn(false);
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true)
            ->willReturn($now);
        $this->paymentTypeEstimationMock->expects($this->once())
            ->method('estimate')
            ->with($subscriptionMock, $now, null)
            ->willReturn([PaymentInfo::PAYMENT_TYPE_INITIAL]);
        $this->nextPaymentDateMock->expects($this->once())
            ->method('getDateInitial')
            ->with($startDate)
            ->willReturn($initialDate);
        $this->setUpSchedule($paymentMock, $subscriptionId, PaymentInfo::PAYMENT_TYPE_INITIAL, $initialDate);

        $this->assertEquals([$paymentMock], $this->scheduler->scheduleInitial($subscriptionMock));
    }

    public function testScheduleInitialNoPayments()
    {
        $subscriptionId = 1;
        $now = '2017-08-01 15:03:00';
        $startDate = '2017-08-01';

        /** @var Subscription|\PHPUnit_Framework_MockObject_MockObject $subscriptionMock */
        $subscriptionMock = $this->createMock(Subscription::class);

        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->with($subscriptionMock)
            ->willReturn(true);
        $subscriptionMock->expects($this->once())
            ->method('__call')
            ->willReturnMap(
                [
                    ['getSubscriptionId', [], $subscriptionId],
                    ['getStartDate', [], $startDate]
                ]
            );
        $this->paymentRepoMock->expects($this->once())
            ->method('has')
            ->with(
                [
                    ['status', Payment::STATUS_PENDING],
                    ['subscription_id', $subscriptionId]
                ]
            )
            ->willReturn(false);
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true)
            ->willReturn($now);
        $this->paymentTypeEstimationMock->expects($this->once())
            ->method('estimate')
            ->with($subscriptionMock, $now, null)
            ->willReturn([]);
        $this->setUpScheduleNoCall();

        $this->assertEquals([], $this->scheduler->scheduleInitial($subscriptionMock));
    }

    public function testScheduleNextHasPayments()
    {
        $subscriptionId = 1;
        $billingPeriod = 'day';
        $billingFrequency = 2;
        $now = '2017-08-01 15:03:00';
        $nextPaymentDate = '2017-08-03';

        /** @var Subscription|\PHPUnit_Framework_MockObject_MockObject $subscriptionMock */
        $subscriptionMock = $this->createMock(Subscription::class);
        $paymentMock = $this->createMock(Payment::class);

        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->with($subscriptionMock)
            ->willReturn(true);
        $subscriptionMock->expects($this->exactly(4))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getSubscriptionId', [], $subscriptionId],
                    ['getBillingPeriod', [], $billingPeriod],
                    ['getBillingFrequency', [], $billingFrequency],
                ]
            );
        $this->paymentRepoMock->expects($this->once())
            ->method('has')
            ->with(
                [
                    ['status', Payment::STATUS_PENDING],
                    ['subscription_id', $subscriptionId]
                ]
            )
            ->willReturn(false);
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true)
            ->willReturn($now);
        $this->nextPaymentDateMock->expects($this->once())
            ->method('getDateNext')
            ->with($now, $billingPeriod, $billingFrequency)
            ->willReturn($nextPaymentDate);
        $this->paymentTypeEstimationMock->expects($this->once())
            ->method('estimate')
            ->with($subscriptionMock, $nextPaymentDate, $now)
            ->willReturn([PaymentInfo::PAYMENT_TYPE_REGULAR]);
        $this->setUpSchedule($paymentMock, $subscriptionId, PaymentInfo::PAYMENT_TYPE_REGULAR, $nextPaymentDate);

        $this->scheduler->scheduleNext($subscriptionMock);
    }

    public function testScheduleNextNoPayments()
    {
        $subscriptionId = 1;
        $billingPeriod = 'day';
        $billingFrequency = 2;
        $now = '2017-08-01 15:03:00';
        $nextPaymentDate = '2017-08-03';

        /** @var Subscription|\PHPUnit_Framework_MockObject_MockObject $subscriptionMock */
        $subscriptionMock = $this->createMock(Subscription::class);

        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->with($subscriptionMock)
            ->willReturn(true);
        $subscriptionMock->expects($this->exactly(3))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getSubscriptionId', [], $subscriptionId],
                    ['getBillingPeriod', [], $billingPeriod],
                    ['getBillingFrequency', [], $billingFrequency],
                ]
            );
        $this->paymentRepoMock->expects($this->once())
            ->method('has')
            ->with(
                [
                    ['status', Payment::STATUS_PENDING],
                    ['subscription_id', $subscriptionId]
                ]
            )
            ->willReturn(false);
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true)
            ->willReturn($now);
        $this->nextPaymentDateMock->expects($this->once())
            ->method('getDateNext')
            ->with($now, $billingPeriod, $billingFrequency)
            ->willReturn($nextPaymentDate);
        $this->paymentTypeEstimationMock->expects($this->once())
            ->method('estimate')
            ->with($subscriptionMock, $nextPaymentDate, $now)
            ->willReturn([]);
        $this->setUpScheduleNoCall();

        $this->scheduler->scheduleNext($subscriptionMock);
    }

    public function testScheduleNextConsecutiveCalls()
    {
        $subscriptionId = 1;
        $billingPeriod = 'day';
        $billingFrequency = 2;
        $now = '2017-08-01 15:03:00';
        $nextPaymentDate = '2017-08-03';

        /** @var Subscription|\PHPUnit_Framework_MockObject_MockObject $subscriptionMock */
        $subscriptionMock = $this->createMock(Subscription::class);
        $paymentMock = $this->createMock(Payment::class);

        $this->payableCheckerMock->expects($this->exactly(2))
            ->method('isPayable')
            ->with($subscriptionMock)
            ->willReturn(true);
        $subscriptionMock->expects($this->exactly(5))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getSubscriptionId', [], $subscriptionId],
                    ['getBillingPeriod', [], $billingPeriod],
                    ['getBillingFrequency', [], $billingFrequency],
                ]
            );
        $this->paymentRepoMock->expects($this->exactly(2))
            ->method('has')
            ->with(
                [
                    ['status', Payment::STATUS_PENDING],
                    ['subscription_id', $subscriptionId]
                ]
            )
            ->willReturnOnConsecutiveCalls(false, true);
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true)
            ->willReturn($now);
        $this->nextPaymentDateMock->expects($this->once())
            ->method('getDateNext')
            ->with($now, $billingPeriod, $billingFrequency)
            ->willReturn($nextPaymentDate);
        $this->paymentTypeEstimationMock->expects($this->once())
            ->method('estimate')
            ->with($subscriptionMock, $nextPaymentDate, $now)
            ->willReturn([PaymentInfo::PAYMENT_TYPE_REGULAR]);
        $this->setUpSchedule($paymentMock, $subscriptionId, PaymentInfo::PAYMENT_TYPE_REGULAR, $nextPaymentDate);

        $this->scheduler->scheduleNext($subscriptionMock);
        $this->scheduler->scheduleNext($subscriptionMock);
    }

    public function testScheduleNextNoPayable()
    {
        /** @var Subscription|\PHPUnit_Framework_MockObject_MockObject $subscriptionMock */
        $subscriptionMock = $this->createMock(Subscription::class);

        $this->payableCheckerMock->expects($this->once())
            ->method('isPayable')
            ->willReturn(false);
        $this->setUpScheduleNoCall();

        $this->scheduler->scheduleNext($subscriptionMock);
    }

    public function testScheduleReattemptPaymentRetry()
    {
        $retries = 2;
        $now = '2017-08-03 15:03:00';
        $scheduledAt = '2017-08-01';
        $nextPaymentDate = '2017-09-01';
        $nextRetryDate = '2017-08-04';
        $lastRetryDate = '2017-08-05';
        $billingPeriod = 'month';
        $billingFrequency = 1;

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        /** @var Subscription|\PHPUnit_Framework_MockObject_MockObject $subscriptionMock */
        $subscriptionMock = $this->createMock(Subscription::class);

        $paymentMock->expects($this->exactly(5))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getRetriesCount', [], $retries],
                    ['getScheduledAt', [], $scheduledAt],
                    ['setStatus', [Payment::STATUS_RETRYING], $paymentMock],
                    ['setRetryAt', [$nextRetryDate], $paymentMock],
                    ['setRetriesCount', [$retries + 1], $paymentMock]
                ]
            );
        $subscriptionMock->expects($this->exactly(2))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getBillingPeriod', [], $billingPeriod],
                    ['getBillingFrequency', [], $billingFrequency]
                ]
            );
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true)
            ->willReturn($now);
        $this->nextPaymentDateMock->expects($this->once())
            ->method('getDateNext')
            ->with($scheduledAt, $billingPeriod, $billingFrequency)
            ->willReturn($nextPaymentDate);
        $this->nextReattemptDateMock->expects($this->once())
            ->method('getLastDate')
            ->with($now, $retries)
            ->willReturn($lastRetryDate);
        $this->coreDateMock->expects($this->exactly(2))
            ->method('gmtTimestamp')
            ->willReturnMap(
                [
                    [$nextPaymentDate, 2],
                    [$lastRetryDate, 1]
                ]
            );
        $this->nextReattemptDateMock->expects($this->once())
            ->method('getDateNext')
            ->with($now, $retries)
            ->willReturn($nextRetryDate);
        $this->paymentRepoMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);

        $this->scheduler->scheduleReattempt($subscriptionMock, $paymentMock);
    }

    public function testScheduleReattemptPlannedPayment()
    {
        $subscriptionId = 1;
        $retries = 2;
        $now = '2017-08-01 15:03:00';
        $scheduledAt = '2017-08-01';
        $nextPaymentDate = '2017-08-02';
        $lastRetryDate = '2017-08-04';
        $billingPeriod = 'day';
        $billingFrequency = 1;

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $newPaymentMock = $this->createMock(Payment::class);
        /** @var Subscription|\PHPUnit_Framework_MockObject_MockObject $subscriptionMock */
        $subscriptionMock = $this->createMock(Subscription::class);

        $paymentMock->expects($this->exactly(3))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getRetriesCount', [], $retries],
                    ['getScheduledAt', [], $scheduledAt],
                    ['setStatus', [Payment::STATUS_FAILED], $paymentMock]
                ]
            );
        $subscriptionMock->expects($this->exactly(3))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getBillingPeriod', [], $billingPeriod],
                    ['getBillingFrequency', [], $billingFrequency],
                    ['getSubscriptionId', [], $subscriptionId]
                ]
            );
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true)
            ->willReturn($now);
        $this->nextPaymentDateMock->expects($this->once())
            ->method('getDateNext')
            ->with($scheduledAt, $billingPeriod, $billingFrequency)
            ->willReturn($nextPaymentDate);
        $this->nextReattemptDateMock->expects($this->once())
            ->method('getLastDate')
            ->with($now, $retries)
            ->willReturn($lastRetryDate);
        $this->coreDateMock->expects($this->exactly(2))
            ->method('gmtTimestamp')
            ->willReturnMap(
                [
                    [$nextPaymentDate, 1],
                    [$lastRetryDate, 2]
                ]
            );
        $this->paymentTypeEstimationMock->expects($this->once())
            ->method('estimate')
            ->with($subscriptionMock, $nextPaymentDate, $now)
            ->willReturn([PaymentInfo::PAYMENT_TYPE_REGULAR]);
        $this->paymentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($newPaymentMock);
        $newPaymentMock->expects($this->exactly(4))
            ->method('__call')
            ->withConsecutive(
                ['setSubscriptionId', [$subscriptionId]],
                ['setStatus', [Payment::STATUS_PENDING]],
                ['setType', [PaymentInfo::PAYMENT_TYPE_REGULAR]],
                ['setScheduledAt', [$nextPaymentDate]]
            )
            ->willReturnSelf();
        $this->paymentRepoMock->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive($newPaymentMock, $paymentMock);

        $this->scheduler->scheduleReattempt($subscriptionMock, $paymentMock);
    }

    public function testReschedule()
    {
        $today = '2017-08-01';

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        /** @var Subscription|\PHPUnit_Framework_MockObject_MockObject $subscriptionMock */
        $subscriptionMock = $this->createMock(Subscription::class);

        $subscriptionMock->expects($this->once())
            ->method('__call')
            ->with('getIsReactivated')
            ->willReturn(false);
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true, false)
            ->willReturn($today);
        $paymentMock->expects($this->once())
            ->method('__call')
            ->with('setScheduledAt', [$today]);
        $this->paymentRepoMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);

        $this->assertSame($paymentMock, $this->scheduler->reschedule($subscriptionMock, $paymentMock));
    }

    public function testRescheduleReactivated()
    {
        $billingPeriod = 'day';
        $billingFrequency = 2;
        $scheduleDate = '2017-08-01';
        $rescheduleDate = '2017-08-03';

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        /** @var Subscription|\PHPUnit_Framework_MockObject_MockObject $subscriptionMock */
        $subscriptionMock = $this->createMock(Subscription::class);

        $subscriptionMock->expects($this->exactly(4))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getIsReactivated', [], true],
                    ['getBillingPeriod', [], $billingPeriod],
                    ['getBillingFrequency', [], $billingFrequency],
                    ['setIsReactivated', [false], $this->returnSelf()]
                ]
            );
        $paymentMock->expects($this->exactly(2))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getScheduledAt', [], $scheduleDate],
                    ['setScheduledAt', [$rescheduleDate], $this->returnSelf()]
                ]
            );
        $this->nextPaymentDateMock->expects($this->once())
            ->method('getDateNextForOutstanding')
            ->with($scheduleDate, $billingPeriod, $billingFrequency)
            ->willReturn($rescheduleDate);
        $this->subscriptionRepoMock->expects($this->once())
            ->method('save')
            ->with($subscriptionMock);
        $this->paymentRepoMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);

        $this->assertSame($paymentMock, $this->scheduler->reschedule($subscriptionMock, $paymentMock));
    }

    /**
     * Set up mocks for schedule() method
     *
     * @param Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock
     * @param int $subscriptionId
     * @param string $type
     * @param string $date
     */
    private function setUpSchedule($paymentMock, $subscriptionId, $type, $date)
    {
        $this->paymentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($paymentMock);
        $paymentMock->expects($this->exactly(4))
            ->method('__call')
            ->withConsecutive(
                ['setSubscriptionId', [$subscriptionId]],
                ['setStatus', [Payment::STATUS_PENDING]],
                ['setType', [$type]],
                ['setScheduledAt', [$date]]
            )
            ->willReturnSelf();
        $this->paymentRepoMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);
    }

    /**
     * Set up mocks for schedule() method for expectation to be never executed
     */
    private function setUpScheduleNoCall()
    {
        $this->paymentFactoryMock->expects($this->never())->method('create');
        $this->paymentRepoMock->expects($this->never())->method('save');
    }
}
