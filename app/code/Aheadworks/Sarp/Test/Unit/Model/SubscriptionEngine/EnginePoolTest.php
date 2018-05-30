<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine;

use Aheadworks\Sarp\Model\SubscriptionEngine\EngineInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionEngine\EnginePool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\EnginePool
 */
class EnginePoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EnginePool
     */
    private $enginePool;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var EngineMetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineMetadataPoolMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->engineMetadataPoolMock = $this->createMock(EngineMetadataPool::class);
        $this->enginePool = $objectManager->getObject(
            EnginePool::class,
            [
                'objectManager' => $this->objectManagerMock,
                'engineMetadataPool' => $this->engineMetadataPoolMock
            ]
        );
    }

    public function testGetEngine()
    {
        $engineCode = 'engine_code';
        $engineClassName = 'SubscriptionEngine';

        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $engineMock = $this->getMockForAbstractClass(EngineInterface::class);

        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($engineCode)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getClassName')
            ->willReturn($engineClassName);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($engineClassName)
            ->willReturn($engineMock);

        $this->assertEquals($engineMock, $this->enginePool->getEngine($engineCode));
    }

    public function testGetEngineCaching()
    {
        $engineCode = 'engine_code';
        $engineClassName = 'SubscriptionEngine';

        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $engineMock = $this->getMockForAbstractClass(EngineInterface::class);

        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($engineCode)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getClassName')
            ->willReturn($engineClassName);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($engineClassName)
            ->willReturn($engineMock);

        $this->enginePool->getEngine($engineCode);
        $this->enginePool->getEngine($engineCode);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Subscription engine engine_code does not implement required interface.
     */
    public function testGetEngineException()
    {
        $engineCode = 'engine_code';
        $engineClassName = 'WrongEngineClassName';

        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $engineMock = $this->getMockBuilder($engineClassName)->getMock();
        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($engineCode)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getClassName')
            ->willReturn($engineClassName);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($engineClassName)
            ->willReturn($engineMock);

        $this->enginePool->getEngine($engineCode);
    }
}
