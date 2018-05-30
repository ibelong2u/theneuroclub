<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine;

use Aheadworks\Sarp\Model\SubscriptionEngine\EngineAvailability;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool
 */
class EngineMetadataPoolTest extends \PHPUnit\Framework\TestCase
{
    const ENGINE_CODE = 'engine_code';

    /**
     * @var array
     */
    private $engineMetadata = ['fieldName' => 'fieldValue'];

    /**
     * @var EngineMetadataPool
     */
    private $metadataPool;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var EngineAvailability|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineAvailabilityMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->engineAvailabilityMock = $this->createMock(EngineAvailability::class);
        $this->metadataPool = $objectManager->getObject(
            EngineMetadataPool::class,
            [
                'objectManager' => $this->objectManagerMock,
                'engineAvailability' => $this->engineAvailabilityMock,
                'metadata' => [self::ENGINE_CODE => $this->engineMetadata]
            ]
        );
    }

    public function testGetMetadata()
    {
        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                EngineMetadataInterface::class,
                ['data' => $this->engineMetadata]
            )
            ->willReturn($metadataMock);
        $this->assertEquals($metadataMock, $this->metadataPool->getMetadata(self::ENGINE_CODE));
    }

    public function testGetMetadataCaching()
    {
        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                EngineMetadataInterface::class,
                ['data' => $this->engineMetadata]
            )
            ->willReturn($metadataMock);
        $this->metadataPool->getMetadata(self::ENGINE_CODE);
        $this->metadataPool->getMetadata(self::ENGINE_CODE);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown subscription engine metadata: nonexistent_engine_code requested
     */
    public function testGetMetadataException()
    {
        $this->metadataPool->getMetadata('nonexistent_engine_code');
    }

    /**
     * @param bool $isAvailable
     * @param array $result
     * @dataProvider getEnginesCodesDataProvider
     */
    public function testGetEnginesCodes($isAvailable, $result)
    {
        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                EngineMetadataInterface::class,
                ['data' => $this->engineMetadata]
            )
            ->willReturn($metadataMock);
        $this->engineAvailabilityMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn($isAvailable);

        $this->assertEquals($result, $this->metadataPool->getEnginesCodes());
    }

    public function testGetEnginesCodesAll()
    {
        $this->assertEquals([self::ENGINE_CODE], $this->metadataPool->getEnginesCodes(false));
    }

    public function getEnginesCodesDataProvider()
    {
        return [[true, [self::ENGINE_CODE]], [false, []]];
    }
}
