<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\DataSource;

use Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\Provider;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\SourceFactory;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\Provider
 */
class ProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var SourceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceFactoryMock;

    /**
     * @var EngineMetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineMetadataPoolMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->sourceFactoryMock = $this->createMock(SourceFactory::class);
        $this->engineMetadataPoolMock = $this->createMock(EngineMetadataPool::class);
        $this->provider = $objectManager->getObject(
            Provider::class,
            [
                'sourceFactory' => $this->sourceFactoryMock,
                'engineMetadataPool' => $this->engineMetadataPoolMock
            ]
        );
    }

    public function testGetDataSource()
    {
        $field = 'fieldName';
        $engineCode = 'paypal';
        $sourceClassName = 'SourceClassName';

        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $sourceMock = $this->getMockForAbstractClass(OptionSourceInterface::class);

        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($engineCode)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getDataSources')
            ->willReturn([$field => $sourceClassName]);
        $this->sourceFactoryMock->expects($this->once())
            ->method('create')
            ->with('SourceClassName')
            ->willReturn($sourceMock);

        $this->assertSame($sourceMock, $this->provider->getDataSource($field, $engineCode));
        $this->provider->getDataSource($field, $engineCode);
    }

    public function testGetDataSourceNonConfiguredField()
    {
        $engineCode = 'paypal';

        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);

        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($engineCode)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getDataSources')
            ->willReturn(['fieldName' => 'SourceClassName']);

        $this->assertNull($this->provider->getDataSource('nonConfiguredFieldName', $engineCode));
    }
}
