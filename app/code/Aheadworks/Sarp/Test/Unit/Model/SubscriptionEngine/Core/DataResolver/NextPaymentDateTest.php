<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Core\DataResolver;

use Aheadworks\Sarp\Model\SubscriptionEngine\Core\DataResolver\NextPaymentDate;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\BillingPeriod;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Core\DataResolver\NextPaymentDate
 */
class NextPaymentDateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NextPaymentDate
     */
    private $resolver;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var CoreDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreDateMock;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->coreDateMock = $this->createMock(CoreDate::class);
        $this->resolver = $this->objectManager->getObject(
            NextPaymentDate::class,
            [
                'dateTime' => $this->dateTimeMock,
                'coreDate' => $this->coreDateMock
            ]
        );
    }

    /**
     * @param int $startTimestamp
     * @param int $nowTimestamp
     * @param int $resultTimestamp
     * @dataProvider getDateInitialDataProvider
     */
    public function testGetDateInitial($startTimestamp, $nowTimestamp, $resultTimestamp)
    {
        $startDate = '2017-08-01';
        $resultDate = '2017-08-02';

        $this->coreDateMock->expects($this->exactly(2))
            ->method('gmtTimestamp')
            ->willReturnMap(
                [
                    [$startDate, $startTimestamp],
                    [null, $nowTimestamp]
                ]
            );
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with($resultTimestamp, false)
            ->willReturn($resultDate);
        $this->assertEquals($resultDate, $this->resolver->getDateInitial($startDate));
    }

    /**
     * @param string $paymentDate
     * @param string $billingPeriod
     * @param int $billingFrequency
     * @param string $result
     * @dataProvider getDateNextDataProvider
     */
    public function testGetDateNext($paymentDate, $billingPeriod, $billingFrequency, $result)
    {
        // Integration test
        $resolver = $this->objectManager->getObject(
            NextPaymentDate::class,
            [
                'dateTime' => $this->objectManager->getObject(DateTime::class)
            ]
        );
        $this->assertEquals(
            $result,
            $resolver->getDateNext($paymentDate, $billingPeriod, $billingFrequency)
        );
    }

    /**
     * @return array
     */
    public function getDateInitialDataProvider()
    {
        return [[1, 2, 2], [2, 1, 2]];
    }

    /**
     * @return array
     */
    public function getDateNextDataProvider()
    {
        return [
            ['2017-08-01', BillingPeriod::DAY, 1, '2017-08-02'],
            ['2017-08-01', BillingPeriod::DAY, 2, '2017-08-03'],
            ['2017-08-01', BillingPeriod::WEEK, 1, '2017-08-08'],
            ['2017-08-01', BillingPeriod::WEEK, 2, '2017-08-15'],
            ['2017-08-01', BillingPeriod::SEMI_MONTH, 1, '2017-08-15'],
            ['2017-08-01', BillingPeriod::SEMI_MONTH, 2, '2017-08-29'],
            ['2017-08-01', BillingPeriod::MONTH, 1, '2017-09-01'],
            ['2017-08-01', BillingPeriod::MONTH, 2, '2017-10-01'],
            ['2017-08-01', BillingPeriod::YEAR, 1, '2018-08-01'],
            ['2017-08-01', BillingPeriod::YEAR, 2, '2019-08-01']
        ];
    }
}
