<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Paypal;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\PlanValidator;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\BillingPeriod;
use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\PlanValidator
 */
class PlanValidatorTest extends \PHPUnit\Framework\TestCase
{
    const ENGINE_LABEL = 'PayPal Express';

    /**
     * @var PlanValidator
     */
    private $validator;

    /**
     * @var EngineMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineMetadataMock;

    /**
     * @var RestrictionsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineRestrictionsMock;

    /**
     * @var array
     */
    private $availableUnitsOfTime = [
        BillingPeriod::DAY,
        BillingPeriod::WEEK,
        BillingPeriod::SEMI_MONTH,
        BillingPeriod::MONTH,
        BillingPeriod::YEAR
    ];

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->engineMetadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $engineMetadataPoolMock = $this->createMock(EngineMetadataPool::class);
        $engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with(PlanValidator::ENGINE_CODE)
            ->willReturn($this->engineMetadataMock);
        $this->engineRestrictionsMock = $this->getMockForAbstractClass(RestrictionsInterface::class);
        $engineRestrictionsPoolMock = $this->createMock(RestrictionsPool::class);
        $engineRestrictionsPoolMock->expects($this->once())
            ->method('getRestrictions')
            ->with(PlanValidator::ENGINE_CODE)
            ->willReturn($this->engineRestrictionsMock);
        $this->validator = $objectManager->getObject(
            PlanValidator::class,
            [
                'engineMetadataPool' => $engineMetadataPoolMock,
                'engineRestrictionsPool' => $engineRestrictionsPoolMock
            ]
        );
    }

    /**
     * @param string $billingPeriod
     * @param int $billingFrequency
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidPeriodDataDataProvider
     */
    public function testIsValidPeriodData(
        $billingPeriod,
        $billingFrequency,
        $expectedResult,
        $expectedMessages
    ) {
        /** @var SubscriptionPlanInterface|\PHPUnit_Framework_MockObject_MockObject $planMock */
        $planMock = $this->getMockForAbstractClass(SubscriptionPlanInterface::class);

        $planMock->expects($this->once())
            ->method('getBillingPeriod')
            ->willReturn($billingPeriod);
        $planMock->expects($this->once())
            ->method('getBillingFrequency')
            ->willReturn($billingFrequency);
        $this->engineRestrictionsMock->expects($this->once())
            ->method('getUnitsOfTime')
            ->willReturn($this->availableUnitsOfTime);
        $this->engineMetadataMock->expects($this->any())
            ->method('getLabel')
            ->willReturn(self::ENGINE_LABEL);
        $this->engineRestrictionsMock->expects($this->any())
            ->method('isInitialFeeSupported')
            ->willReturn(true);
        $this->engineRestrictionsMock->expects($this->any())
            ->method('isTrialPeriodSupported')
            ->willReturn(true);

        $this->assertEquals($expectedResult, $this->validator->isValid($planMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * @param bool $isInitialFeeEnabled
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidInitialFeeNotSupportedDataProvider
     */
    public function testIsValidInitialFeeNotSupported($isInitialFeeEnabled, $expectedResult, $expectedMessages)
    {
        /** @var SubscriptionPlanInterface|\PHPUnit_Framework_MockObject_MockObject $planMock */
        $planMock = $this->getMockForAbstractClass(SubscriptionPlanInterface::class);

        $planMock->expects($this->once())
            ->method('getBillingPeriod')
            ->willReturn(BillingPeriod::DAY);
        $planMock->expects($this->once())
            ->method('getBillingFrequency')
            ->willReturn(10);
        $this->engineRestrictionsMock->expects($this->once())
            ->method('getUnitsOfTime')
            ->willReturn($this->availableUnitsOfTime);
        $this->engineRestrictionsMock->expects($this->once())
            ->method('isInitialFeeSupported')
            ->willReturn(false);
        $this->engineRestrictionsMock->expects($this->once())
            ->method('isTrialPeriodSupported')
            ->willReturn(true);
        $planMock->expects($this->once())
            ->method('getIsInitialFeeEnabled')
            ->willReturn($isInitialFeeEnabled);
        $this->engineMetadataMock->expects($this->any())
            ->method('getLabel')
            ->willReturn(self::ENGINE_LABEL);

        $this->assertEquals($expectedResult, $this->validator->isValid($planMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * @param bool $isTrialPeriodEnabled
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidTrialPeriodNotSupportedDataProvider
     */
    public function testIsValidTrialPeriodNotSupported(
        $isTrialPeriodEnabled,
        $expectedResult,
        $expectedMessages
    ) {
        /** @var SubscriptionPlanInterface|\PHPUnit_Framework_MockObject_MockObject $planMock */
        $planMock = $this->getMockForAbstractClass(SubscriptionPlanInterface::class);

        $planMock->expects($this->once())
            ->method('getBillingPeriod')
            ->willReturn(BillingPeriod::DAY);
        $planMock->expects($this->once())
            ->method('getBillingFrequency')
            ->willReturn(10);
        $this->engineRestrictionsMock->expects($this->once())
            ->method('getUnitsOfTime')
            ->willReturn($this->availableUnitsOfTime);
        $this->engineRestrictionsMock->expects($this->once())
            ->method('isInitialFeeSupported')
            ->willReturn(true);
        $this->engineRestrictionsMock->expects($this->once())
            ->method('isTrialPeriodSupported')
            ->willReturn(false);
        $planMock->expects($this->once())
            ->method('getIsTrialPeriodEnabled')
            ->willReturn($isTrialPeriodEnabled);
        $this->engineMetadataMock->expects($this->any())
            ->method('getLabel')
            ->willReturn(self::ENGINE_LABEL);

        $this->assertEquals($expectedResult, $this->validator->isValid($planMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * @return array
     */
    public function isValidPeriodDataDataProvider()
    {
        return [
            'correct data' => [BillingPeriod::DAY, 10, true, []],
            'billing period not supported' => [
                'nonexistent_period_unit',
                10,
                false,
                ['Billing period doesn\'t supported by PayPal Express subscription engine.']
            ],
            'billing frequency less than 0' => [
                BillingPeriod::DAY,
                -5,
                false,
                ['Billing frequency is invalid.']
            ],
            'combination of billing frequency and billing period greater than one year 1' => [
                BillingPeriod::DAY,
                366,
                false,
                [
                    'The combination of billing period and billing frequency '
                    . 'cannot exceed one year for PayPal Express subscription engine.'
                ]
            ],
            'combination of billing frequency and billing period greater than one year 2' => [
                BillingPeriod::WEEK,
                53,
                false,
                [
                    'The combination of billing period and billing frequency '
                    . 'cannot exceed one year for PayPal Express subscription engine.'
                ]
            ],
            'combination of billing frequency and billing period greater than one year 3' => [
                BillingPeriod::MONTH,
                13,
                false,
                [
                    'The combination of billing period and billing frequency '
                    . 'cannot exceed one year for PayPal Express subscription engine.'
                ]
            ],
            'combination of billing frequency and billing period greater than one year 4' => [
                BillingPeriod::YEAR,
                2,
                false,
                [
                    'The combination of billing period and billing frequency '
                    . 'cannot exceed one year for PayPal Express subscription engine.'
                ]
            ],
            'invalid billing frequency for semi month period' => [
                BillingPeriod::SEMI_MONTH,
                2,
                false,
                [
                    'If the billing period is SemiMonth, '
                    . 'the billing frequency must be 1 for PayPal Express subscription engine.'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function isValidInitialFeeNotSupportedDataProvider()
    {
        return [
            'correct data' => [false, true, []],
            'initial fee enabled' => [
                true,
                false,
                ['Initial fee doesn\'t supported by PayPal Express subscription engine.']
            ]
        ];
    }

    /**
     * @return array
     */
    public function isValidTrialPeriodNotSupportedDataProvider()
    {
        return [
            'correct data' => [false, true, []],
            'trial period enabled' => [
                true,
                false,
                ['Trial period doesn\'t supported by PayPal Express subscription engine.']
            ]
        ];
    }
}
