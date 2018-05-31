<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\Checkout\ConfigProvider;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Model\Checkout\ConfigProvider\SubscriptionPlan as SubscriptionPlanConfigProvider;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\DayOfMonth\Ending;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\StartDateType as StartDateTypeSource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\Checkout\ConfigProvider\SubscriptionPlan
 */
class SubscriptionPlanTest extends \PHPUnit\Framework\TestCase
{
    const START_DATE_TYPE_OPTION_VALUE = 'option_value';
    const START_DATE_TYPE_OPTION_LABEL = 'option_label';

    /**
     * @var SubscriptionPlanConfigProvider
     */
    private $configProvider;

    /**
     * @var StartDateTypeSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $startDateTypeSourceMock;

    /**
     * @var Ending|\PHPUnit_Framework_MockObject_MockObject
     */
    private $endingMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->startDateTypeSourceMock = $this->createMock(StartDateTypeSource::class);
        $this->endingMock = $this->createMock(Ending::class);
        $this->configProvider = $objectManager->getObject(
            SubscriptionPlanConfigProvider::class,
            [
                'startDateTypeSource' => $this->startDateTypeSourceMock,
                'ending' => $this->endingMock
            ]
        );
    }

    /**
     * @param string $startDateType
     * @param int|null $dayOfMonth
     * @param string $result
     * @dataProvider prepareStartValueDataProvider
     */
    public function testPrepareStartValue($startDateType, $dayOfMonth, $result)
    {
        $planMock = $this->getMockForAbstractClass(SubscriptionPlanInterface::class);
        $this->startDateTypeSourceMock->expects($this->once())
            ->method('getOptions')
            ->willReturn(
                [
                    self::START_DATE_TYPE_OPTION_VALUE => self::START_DATE_TYPE_OPTION_LABEL
                ]
            );
        $planMock->expects($this->once())
            ->method('getStartDateType')
            ->willReturn($startDateType);
        if ($startDateType == StartDateTypeSource::EXACT_DAY_OF_MONTH) {
            $planMock->expects($this->once())
                ->method('getStartDateDayOfMonth')
                ->willReturn($dayOfMonth);
            $this->endingMock->expects($this->once())
                ->method('getEnding')
                ->willReturn('st');
        }

        $class = new \ReflectionClass($this->configProvider);
        $method = $class->getMethod('prepareStartValue');
        $method->setAccessible(true);

        $this->assertEquals($result, $method->invokeArgs($this->configProvider, [$planMock]));
    }

    /**
     * @return array
     */
    public function prepareStartValueDataProvider()
    {
        return [
            [self::START_DATE_TYPE_OPTION_VALUE, null, self::START_DATE_TYPE_OPTION_LABEL],
            [StartDateTypeSource::EXACT_DAY_OF_MONTH, 1, '1st day of month']
        ];
    }
}
