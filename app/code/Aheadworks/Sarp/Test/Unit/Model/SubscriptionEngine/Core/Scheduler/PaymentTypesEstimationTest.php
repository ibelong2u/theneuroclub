<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Core\Scheduler;

use Aheadworks\Sarp\Model\Profile\PaymentInfo;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\DataResolver\NextPaymentDate;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Scheduler\PaymentTypesEstimation;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Core\Scheduler\PaymentTypesEstimation
 */
class PaymentTypesEstimationTest extends \PHPUnit\Framework\TestCase
{
    const BILLING_FREQUENCY = 'day';
    const BILLING_PERIOD = 3;

    /**
     * @var PaymentTypesEstimation
     */
    private $estimation;

    /**
     * @var CoreDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreDateMock;

    /**
     * @var NextPaymentDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nextPaymentDateMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->coreDateMock = $this->createMock(CoreDate::class);
        $this->nextPaymentDateMock = $this->createMock(NextPaymentDate::class);
        $this->estimation = $objectManager->getObject(
            PaymentTypesEstimation::class,
            [
                'coreDate' => $this->coreDateMock,
                'nextPaymentDate' => $this->nextPaymentDateMock
            ]
        );
    }

    /**
     * @param bool $isInitialFeeEnabled
     * @param bool $isTrialPeriodEnabled
     * @param string $currentDate
     * @param string $startDate
     * @param array $result
     * @dataProvider estimateInitialDataProvider
     */
    public function testEstimateInitial(
        $isInitialFeeEnabled,
        $isTrialPeriodEnabled,
        $currentDate,
        $startDate,
        $result
    ) {
        $trialTotalBillingCycles = 3;
        $totalBillingCycles = 10;

        /** @var Subscription|\PHPUnit_Framework_MockObject_MockObject $subscriptionMock */
        $subscriptionMock = $this->createMock(Subscription::class);

        $subscriptionMock->expects($this->any())
            ->method('__call')
            ->willReturnMap(
                [
                    ['getStartDate', [], $startDate],
                    ['getIsInitialFeeEnabled', [], $isInitialFeeEnabled],
                    ['getIsInitialPaid', [], false],
                    ['getIsTrialPeriodEnabled', [], $isTrialPeriodEnabled],
                    ['getTrialPaymentsCount', [], 0],
                    ['getTrialTotalBillingCycles', [], $trialTotalBillingCycles],
                    ['getTotalBillingCycles', [], $totalBillingCycles],
                    ['getRegularPaymentsCount', [], 0],
                ]
            );
        $this->coreDateMock->expects($this->exactly(2))
            ->method('gmtTimestamp')
            ->willReturnCallback(
                function ($input) {
                    return strtotime($input);
                }
            );

        $this->assertEquals($result, $this->estimation->estimate($subscriptionMock, $currentDate));
    }

    /**
     * @param Subscription|\PHPUnit_Framework_MockObject_MockObject $subscriptionMock
     * @param string $currentDate
     * @param string $lastPaymentDate
     * @param string $estimatedPaymentDate Estimated next payment date
     * @param array $result
     * @dataProvider estimateWasPaymentsDataProvider
     */
    public function testEstimateWasPayments(
        $subscriptionMock,
        $currentDate,
        $lastPaymentDate,
        $estimatedPaymentDate,
        $result
    ) {
        $this->coreDateMock->expects($this->exactly(3))
            ->method('gmtTimestamp')
            ->willReturnCallback(
                function ($input) {
                    return strtotime($input);
                }
            );
        $this->nextPaymentDateMock->expects($this->once())
            ->method('getDateNext')
            ->with($lastPaymentDate, self::BILLING_PERIOD, self::BILLING_FREQUENCY)
            ->willReturn($estimatedPaymentDate);

        $this->assertEquals($result, $this->estimation->estimate($subscriptionMock, $currentDate, $lastPaymentDate));
    }

    /**
     * @param $map
     * @return Subscription|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createSubscriptionMock($map)
    {
        $map[] = ['getIsInitialFeeEnabled', [], false];
        $map[] = ['getIsInitialPaid', [], true];
        $map[] = ['getBillingPeriod', [], self::BILLING_PERIOD];
        $map[] = ['getBillingFrequency', [], self::BILLING_FREQUENCY];

        $subscriptionMock = $this->createMock(Subscription::class);
        $subscriptionMock->expects($this->any())
            ->method('__call')
            ->willReturnMap($map);
        return $subscriptionMock;
    }

    /**
     * @return array
     */
    public function estimateInitialDataProvider()
    {
        return [
            [false, false, '2017-08-01', '2017-08-02', []],
            [false, true, '2017-08-01', '2017-08-02', []],
            [false, false, '2017-08-01', '2017-08-01', [PaymentInfo::PAYMENT_TYPE_REGULAR]],
            [false, true, '2017-08-01', '2017-08-01', [PaymentInfo::PAYMENT_TYPE_TRIAL]],
            [true, false, '2017-08-01', '2017-08-02', [PaymentInfo::PAYMENT_TYPE_INITIAL]],
            [
                true,
                false,
                '2017-08-01',
                '2017-08-01',
                [PaymentInfo::PAYMENT_TYPE_INITIAL, PaymentInfo::PAYMENT_TYPE_REGULAR]
            ],
            [
                true,
                true,
                '2017-08-01',
                '2017-08-01',
                [PaymentInfo::PAYMENT_TYPE_INITIAL, PaymentInfo::PAYMENT_TYPE_TRIAL]
            ]
        ];
    }

    /**
     * @return array
     */
    public function estimateWasPaymentsDataProvider()
    {
        return [
            [
                $this->createSubscriptionMock(
                    [
                        ['getIsTrialPeriodEnabled', [], false],
                        ['getTotalBillingCycles', [], 1],
                        ['getTrialTotalBillingCycles', [], 0],
                        ['getRegularPaymentsCount', [], 0]
                    ]
                ),
                '2017-08-02',
                '2017-08-01',
                '2017-08-02',
                [PaymentInfo::PAYMENT_TYPE_REGULAR]
            ],
            [
                $this->createSubscriptionMock(
                    [
                        ['getIsTrialPeriodEnabled', [], false],
                        ['getTotalBillingCycles', [], 1],
                        ['getTrialTotalBillingCycles', [], 0],
                        ['getRegularPaymentsCount', [], 1]
                    ]
                ),
                '2017-08-02',
                '2017-08-01',
                '2017-08-02',
                []
            ],
            [
                $this->createSubscriptionMock(
                    [
                        ['getIsTrialPeriodEnabled', [], false],
                        ['getTotalBillingCycles', [], 0],
                        ['getTrialTotalBillingCycles', [], 0],
                        ['getRegularPaymentsCount', [], 10]
                    ]
                ),
                '2017-08-02',
                '2017-08-01',
                '2017-08-02',
                [PaymentInfo::PAYMENT_TYPE_REGULAR]
            ],
            [
                $this->createSubscriptionMock(
                    [
                        ['getIsTrialPeriodEnabled', [], true],
                        ['getTotalBillingCycles', [], 2],
                        ['getTrialTotalBillingCycles', [], 1],
                        ['getRegularPaymentsCount', [], 0],
                        ['getTrialPaymentsCount', [], 0]
                    ]
                ),
                '2017-08-02',
                '2017-08-01',
                '2017-08-02',
                [PaymentInfo::PAYMENT_TYPE_TRIAL]
            ],
            [
                $this->createSubscriptionMock(
                    [
                        ['getIsTrialPeriodEnabled', [], true],
                        ['getTotalBillingCycles', [], 2],
                        ['getTrialTotalBillingCycles', [], 1],
                        ['getRegularPaymentsCount', [], 0],
                        ['getTrialPaymentsCount', [], 1]
                    ]
                ),
                '2017-08-02',
                '2017-08-01',
                '2017-08-02',
                [PaymentInfo::PAYMENT_TYPE_REGULAR]
            ],
            [
                $this->createSubscriptionMock(
                    [
                        ['getIsTrialPeriodEnabled', [], false],
                        ['getTotalBillingCycles', [], 1],
                        ['getTrialTotalBillingCycles', [], 0],
                        ['getRegularPaymentsCount', [], 0]
                    ]
                ),
                '2017-08-02',
                '2017-08-01',
                '2017-08-03',
                []
            ],
            [
                $this->createSubscriptionMock(
                    [
                        ['getIsTrialPeriodEnabled', [], true],
                        ['getTrialPaymentsCount', [], 1],
                        ['getTrialTotalBillingCycles', [], 1],
                        ['getTotalBillingCycles', [], 1],
                        ['getRegularPaymentsCount', [], 0]
                    ]
                ),
                '2017-08-02',
                '2017-08-01',
                '2017-08-02',
                [PaymentInfo::PAYMENT_TYPE_REGULAR]
            ]
        ];
    }
}
