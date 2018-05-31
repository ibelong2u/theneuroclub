<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\ProfileActionValidator;
use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\ProfileActionValidator
 */
class ProfileActionValidatorTest extends \PHPUnit\Framework\TestCase
{
    const ENGINE_CODE = 'engine_code';

    /**
     * @var array
     */
    private $subscriptionActions = ['action1', 'action2'];

    /**
     * @var array
     */
    private $subscriptionActionsMap = [
        'status1' => ['action1'],
        'status2' => ['action2']
    ];

    /**
     * @var ProfileActionValidator
     */
    private $validator;

    /**
     * @var RestrictionsPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restrictionsPoolMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->restrictionsPoolMock = $this->createMock(RestrictionsPool::class);
        $this->validator = $objectManager->getObject(
            ProfileActionValidator::class,
            ['restrictionsPool' => $this->restrictionsPoolMock]
        );
    }

    /**
     * @param string $profileStatus
     * @param string $action
     * @param bool $expectedResult
     * @param string|null $expectedMessage
     * @dataProvider isValidForActionDataProvider
     */
    public function testIsValidForAction($profileStatus, $action, $expectedResult, $expectedMessage)
    {
        /** @var ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        $restrictionsMock = $this->getMockForAbstractClass(RestrictionsInterface::class);

        $profileMock->expects($this->any())
            ->method('getEngineCode')
            ->willReturn(self::ENGINE_CODE);
        $this->restrictionsPoolMock->expects($this->any())
            ->method('getRestrictions')
            ->with(self::ENGINE_CODE)
            ->willReturn($restrictionsMock);
        $restrictionsMock->expects($this->any())
            ->method('getSubscriptionActions')
            ->willReturn($this->subscriptionActions);
        $profileMock->expects($this->any())
            ->method('getStatus')
            ->willReturn($profileStatus);
        $restrictionsMock->expects($this->any())
            ->method('getSubscriptionActionsMap')
            ->willReturn($this->subscriptionActionsMap);

        $this->assertEquals($expectedResult, $this->validator->isValidForAction($profileMock, $action));
        $this->assertEquals($expectedMessage, $this->validator->getMessage());
    }

    /**
     * @return array
     */
    public function isValidForActionDataProvider()
    {
        return [
            'correct data' => ['status1', 'action1', true, null],
            'wrong action' => [
                'status1',
                'nonexistent_action',
                false,
                'Action nonexistent_action is not supported by ' . self::ENGINE_CODE . ' subscription engine.'
            ],
            'wrong action for profile status' => [
                'status1',
                'action2',
                false,
                'Action action2 is not supported for status1 profile status.'
            ]
        ];
    }
}
