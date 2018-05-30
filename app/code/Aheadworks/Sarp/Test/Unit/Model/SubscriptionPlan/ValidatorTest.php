<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionPlan;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionPlanDescriptionInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionEngine\PlanValidatorFactory;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\StartDateType;
use Aheadworks\Sarp\Model\SubscriptionPlan\Validator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionPlan\Validator
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    const ENGINE_CODE = 'engine_code';
    const ENGINE_CODE_NON_EXISTING = 'engine_code_non_existing';

    const ENGINE_SPECIFIC_VALIDATION_MESSAGE = 'Initial fee doesn\'t supported by subscription engine.';

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var EngineMetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineMetadataPoolMock;

    /**
     * @var PlanValidatorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineSpecificValidatorFactoryMock;

    /**
     * @var array
     */
    private $subscriptionPlanData = [
        'getWebsiteId' => 1,
        'getName' => 'Subscription plan',
        'getEngineCode' => self::ENGINE_CODE,
        'getIsTrialPeriodEnabled' => true,
        'getTotalBillingCycles' => 10,
        'getStartDateType' => StartDateType::EXACT_DAY_OF_MONTH,
        'getStartDateDayOfMonth' => 5,
        'getTrialTotalBillingCycles' => 2
    ];

    /**
     * @var array
     */
    private $subscriptionPlanDescriptionData = [
        'getStoreId' => 0,
        'getTitle' => 'Subscription plan',
        'getDescription' => 'Subscription plan description'
    ];

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->engineSpecificValidatorFactoryMock = $this->createMock(PlanValidatorFactory::class);
        $this->engineMetadataPoolMock = $this->createMock(EngineMetadataPool::class);
        $this->validator = $objectManager->getObject(
            Validator::class,
            [
                'engineMetadataPool' => $this->engineMetadataPoolMock,
                'engineSpecificValidatorFactory' => $this->engineSpecificValidatorFactoryMock
            ]
        );
    }

    /**
     * @param SubscriptionPlanInterface|\PHPUnit_Framework_MockObject_MockObject $subscriptionPlanMock
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($subscriptionPlanMock, $expectedResult, $expectedMessages)
    {
        $engineSpecificValidatorClassName = 'PlanValidator';

        $engineMetadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $engineSpecificValidatorMock = $this->getMockForAbstractClass(\Zend_Validate_Interface::class);

        $this->engineMetadataPoolMock->expects($this->any())
            ->method('getEnginesCodes')
            ->willReturn([self::ENGINE_CODE]);
        $this->engineMetadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(self::ENGINE_CODE)
            ->willReturn($engineMetadataMock);
        $engineMetadataMock->expects($this->any())
            ->method('getPlanValidatorClassName')
            ->willReturn($engineSpecificValidatorClassName);
        $this->engineSpecificValidatorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($engineSpecificValidatorMock);
        $engineSpecificValidatorMock->expects($this->any())
            ->method('isValid')
            ->with($subscriptionPlanMock)
            ->willReturn(true);

        $this->assertEquals($expectedResult, $this->validator->isValid($subscriptionPlanMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * @param SubscriptionPlanInterface|\PHPUnit_Framework_MockObject_MockObject $subscriptionPlanMock
     * @param bool $isDataValid
     * @param array $expectedMessages
     * @dataProvider isEngineSpecificDataValidDataProvider
     */
    public function testIsEngineSpecificDataValid(
        $subscriptionPlanMock,
        $isDataValid,
        $expectedMessages
    ) {
        $engineSpecificValidatorClassName = 'PlanValidator';

        $engineMetadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $engineSpecificValidatorMock = $this->getMockForAbstractClass(\Zend_Validate_Interface::class);

        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with(self::ENGINE_CODE)
            ->willReturn($engineMetadataMock);
        $engineMetadataMock->expects($this->once())
            ->method('getPlanValidatorClassName')
            ->willReturn($engineSpecificValidatorClassName);
        $this->engineSpecificValidatorFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($engineSpecificValidatorMock);
        $engineSpecificValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($subscriptionPlanMock)
            ->willReturn($isDataValid);
        $engineSpecificValidatorMock->expects($this->any())
            ->method('getMessages')
            ->willReturn($expectedMessages);

        $class = new \ReflectionClass($this->validator);
        $method = $class->getMethod('isEngineSpecificDataValid');
        $method->setAccessible(true);

        $this->assertEquals($isDataValid, $method->invokeArgs($this->validator, [$subscriptionPlanMock]));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * @param SubscriptionPlanDescriptionInterface|\PHPUnit_Framework_MockObject_MockObject $descriptionMock
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isDescriptionsDataValidDataProvider
     */
    public function testIsDescriptionsDataValid($descriptionMock, $expectedResult, $expectedMessages)
    {
        /** @var SubscriptionPlanInterface|\PHPUnit_Framework_MockObject_MockObject $subscriptionPlanMock */
        $subscriptionPlanMock = $this->getMockForAbstractClass(SubscriptionPlanInterface::class);
        $subscriptionPlanMock->expects($this->any())
            ->method('getDescriptions')
            ->willReturn([$descriptionMock]);

        $class = new \ReflectionClass($this->validator);
        $method = $class->getMethod('isDescriptionsDataValid');
        $method->setAccessible(true);

        $this->assertEquals($expectedResult, $method->invokeArgs($this->validator, [$subscriptionPlanMock]));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * Create subscription plan mock and optionally modify getter result
     *
     * @param string|null $methodModify
     * @param mixed|null $valueModify
     * @return SubscriptionPlanInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createSubscriptionPlanMock($methodModify = null, $valueModify = null)
    {
        /** @var SubscriptionPlanInterface|\PHPUnit_Framework_MockObject_MockObject $subscriptionPlanMock */
        $subscriptionPlanMock = $this->getMockForAbstractClass(SubscriptionPlanInterface::class);
        foreach ($this->subscriptionPlanData as $method => $value) {
            if ($method != $methodModify) {
                $subscriptionPlanMock->expects($this->any())
                    ->method($method)
                    ->willReturn($value);
            } else {
                $subscriptionPlanMock->expects($this->any())
                    ->method($methodModify)
                    ->willReturn($valueModify);
            }
        }
        $subscriptionPlanMock->expects($this->any())
            ->method('getDescriptions')
            ->willReturn([$this->createSubscriptionPlanDescriptionMock()]);
        return $subscriptionPlanMock;
    }

    /**
     * Create subscription plan description mock and optionally modify getter result
     *
     * @param string|null $methodModify
     * @param mixed|null $valueModify
     * @return SubscriptionPlanDescriptionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createSubscriptionPlanDescriptionMock($methodModify = null, $valueModify = null)
    {
        $subscriptionPlanDescriptionMock = $this->getMockForAbstractClass(SubscriptionPlanDescriptionInterface::class);
        foreach ($this->subscriptionPlanDescriptionData as $method => $value) {
            if ($method != $methodModify) {
                $subscriptionPlanDescriptionMock->expects($this->any())
                    ->method($method)
                    ->willReturn($value);
            } else {
                $subscriptionPlanDescriptionMock->expects($this->any())
                    ->method($methodModify)
                    ->willReturn($valueModify);
            }
        }
        return $subscriptionPlanDescriptionMock;
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            'correct data' => [$this->createSubscriptionPlanMock(), true, []],
            'missing engine code' => [
                $this->createSubscriptionPlanMock('getEngineCode', null),
                false,
                ['Subscription engine is required.']
            ],
            'incorrect engine code' => [
                $this->createSubscriptionPlanMock('getEngineCode', self::ENGINE_CODE_NON_EXISTING),
                false,
                ['Subscription engine code is incorrect or unavailable.']
            ],
            'missing website ID' => [
                $this->createSubscriptionPlanMock('getWebsiteId', null),
                false,
                ['Select website.']
            ],
            'missing name' => [
                $this->createSubscriptionPlanMock('getName', null),
                false,
                ['Name is required.']
            ],
            'total billing cycles is not numeric' => [
                $this->createSubscriptionPlanMock('getTotalBillingCycles', 'string'),
                false,
                ['Number of payments is not a number.']
            ],
            'missing start date day of month' => [
                $this->createSubscriptionPlanMock('getStartDateDayOfMonth', null),
                false,
                ['Day of month is required.']
            ],
            'start date day of month is not numeric' => [
                $this->createSubscriptionPlanMock('getStartDateDayOfMonth', 'string'),
                false,
                ['Day of month is not a number.']
            ],
            'start date day of month equals 0' => [
                $this->createSubscriptionPlanMock('getStartDateDayOfMonth', 0),
                false,
                ['Day of month must be greater than 0.']
            ],
            'missing trial total billing cycles' => [
                $this->createSubscriptionPlanMock('getTrialTotalBillingCycles', null),
                false,
                ['Number of trial payments is required.']
            ],
            'trial total billing cycles is not numeric' => [
                $this->createSubscriptionPlanMock('getTrialTotalBillingCycles', 'string'),
                false,
                ['Number of trial payments is not a number.']
            ],
            'trial total billing cycles equals 0' => [
                $this->createSubscriptionPlanMock('getTrialTotalBillingCycles', 0),
                false,
                ['Number of trial payments must be greater than 0.']
            ]
        ];
    }

    /**
     * @return array
     */
    public function isEngineSpecificDataValidDataProvider()
    {
        return [
            'correct data' => [$this->createSubscriptionPlanMock(), true, []],
            'invalid data' => [
                $this->createSubscriptionPlanMock(),
                false,
                [self::ENGINE_SPECIFIC_VALIDATION_MESSAGE]
            ]
        ];
    }

    /**
     * @return array
     */
    public function isDescriptionsDataValidDataProvider()
    {
        return [
            'correct data' => [$this->createSubscriptionPlanDescriptionMock(), true, []],
            'missing title' => [
                $this->createSubscriptionPlanDescriptionMock('getTitle', null),
                false,
                ['Storefront title is required.']
            ],
            'description length greater than 256' => [
                $this->createSubscriptionPlanDescriptionMock(
                    'getDescription',
                    'Lorem Ipsum is simply dummy text of the printing and typesetting industry.' .
                    'Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, ' .
                    'when an unknown printer took a galley of type and scrambled it to make a type specimen book. ' .
                    'It has survived not only five centuries, but also the leap into electronic typesetting, ' .
                    'remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset ' .
                    'sheets containing Lorem Ipsum passages, and more recently with desktop publishing software ' .
                    'like Aldus PageMaker including versions of Lorem Ipsum.'
                ),
                false,
                ['Storefront description is more than 256 characters long.']
            ],
        ];
    }
}
