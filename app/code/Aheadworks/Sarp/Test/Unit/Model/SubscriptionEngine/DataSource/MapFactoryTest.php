<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\DataSource;

use Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\MapFactory;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\MapInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\MapFactory
 */
class MapFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MapFactory
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
            MapFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $mapClassName = 'DataSourceMap';

        $mapMock = $this->getMockForAbstractClass(MapInterface::class);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($mapClassName)
            ->willReturn($mapMock);
        $this->assertEquals($mapMock, $this->factory->create($mapClassName));
    }
}
