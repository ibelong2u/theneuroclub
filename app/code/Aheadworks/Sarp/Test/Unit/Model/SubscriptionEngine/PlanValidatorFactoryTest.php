<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine;

use Aheadworks\Sarp\Model\SubscriptionEngine\PlanValidatorFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\PlanValidatorFactory
 */
class PlanValidatorFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PlanValidatorFactory
     */
    private $factory;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->factory = $objectManager->getObject(
            PlanValidatorFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $validatorClassName = 'PlanValidator';

        $validatorMock = $this->getMockForAbstractClass(\Zend_Validate_Interface::class);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($validatorClassName)
            ->willReturn($validatorMock);
        $this->assertEquals($validatorMock, $this->factory->create($validatorClassName));
    }
}
