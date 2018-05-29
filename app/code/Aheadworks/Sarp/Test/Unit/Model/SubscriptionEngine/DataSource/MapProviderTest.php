<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\DataSource;

use Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\MapProvider;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\MapFactory;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\MapInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\MapProvider
 */
class MapProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MapProvider
     */
    private $provider;

    /**
     * @var MapFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mapFactoryMock;

    /**
     * @var EngineMetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineMetadataPoolMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->mapFactoryMock = $this->createMock(MapFactory::class);
        $this->engineMetadataPoolMock = $this->createMock(EngineMetadataPool::class);
        $this->provider = $objectManager->getObject(
            MapProvider::class,
            [
                'mapFactory' => $this->mapFactoryMock,
                'engineMetadataPool' => $this->engineMetadataPoolMock
            ]
        );
    }

    public function testGetMap()
    {
        $fromField = 'fromFieldName';
        $toField = 'toFieldName';
        $engineCode = 'paypal';
        $mapClassName = 'MapClassName';

        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $mapMock = $this->getMockForAbstractClass(MapInterface::class);

        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($engineCode)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getDataSourceMaps')
            ->willReturn([$fromField => [$toField => $mapClassName]]);
        $this->mapFactoryMock->expects($this->once())
            ->method('create')
            ->with('MapClassName')
            ->willReturn($mapMock);

        $this->assertSame($mapMock, $this->provider->getMap($fromField, $toField, $engineCode));
        $this->provider->getMap($fromField, $toField, $engineCode);
    }

    /**
     * @param array $dataSourceMaps
     * @param string $fromField
     * @param string $toField
     * @dataProvider getMapNonConfiguredFieldDataProvider
     */
    public function testGetMapNonConfiguredField($dataSourceMaps, $fromField, $toField)
    {
        $engineCode = 'paypal';

        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);

        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($engineCode)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getDataSourceMaps')
            ->willReturn($dataSourceMaps);

        $this->assertNull($this->provider->getMap($fromField, $toField, $engineCode));
    }

    /**
     * @return array
     */
    public function getMapNonConfiguredFieldDataProvider()
    {
        return [
            [
                ['fromFieldName' => ['toFieldName' => 'MapClassName']],
                'nonConfiguredFromFieldName',
                'toFieldName'
            ],
            [
                ['fromFieldName' => ['toFieldName' => 'MapClassName']],
                'fromFieldName',
                'nonConfiguredToFieldName'
            ]
        ];
    }
}
