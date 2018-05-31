<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine;

use Aheadworks\Sarp\Model\Checkout\ConfigProviderInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\CheckoutConfigFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\CheckoutConfigFactory
 */
class CheckoutConfigFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CheckoutConfigFactory
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
            CheckoutConfigFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $configProviderClassName = 'ConfigProvider';

        $configProviderMock = $this->getMockForAbstractClass(ConfigProviderInterface::class);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($configProviderClassName)
            ->willReturn($configProviderMock);
        $this->assertEquals($configProviderMock, $this->factory->create($configProviderClassName));
    }
}
