<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine;

use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Restrictions\Provider as CoreRestrictionsProvider;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionEngine\Restrictions;
use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsInterfaceFactory;
use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsPool
 */
class RestrictionsPoolTest extends \PHPUnit\Framework\TestCase
{
    const ENGINE_CODE = 'engine_code';

    /**
     * @var array
     */
    private $engineRestrictions = ['configFieldName' => 'configFieldValue'];

    /**
     * @var array
     */
    private $coreRestrictions = ['coreFieldName' => 'coreFieldValue'];

    /**
     * @var RestrictionsPool
     */
    private $restrictionsPool;

    /**
     * @var RestrictionsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restrictionsFactoryMock;

    /**
     * @var EngineMetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineMetadataPoolMock;

    /**
     * @var CoreRestrictionsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreRestrictionsProviderMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->restrictionsFactoryMock = $this->createMock(RestrictionsInterfaceFactory::class);
        $this->engineMetadataPoolMock = $this->createMock(EngineMetadataPool::class);
        $this->coreRestrictionsProviderMock = $this->createMock(CoreRestrictionsProvider::class);
        $this->restrictionsPool = $objectManager->getObject(
            RestrictionsPool::class,
            [
                'restrictionsFactory' => $this->restrictionsFactoryMock,
                'engineMetadataPool' => $this->engineMetadataPoolMock,
                'coreRestrictionsProvider' => $this->coreRestrictionsProviderMock,
                'restrictions' => [self::ENGINE_CODE => $this->engineRestrictions]
            ]
        );
    }

    public function testGetRestrictionsForGatewayEngine()
    {
        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $restrictionsMock = $this->getMockForAbstractClass(RestrictionsInterface::class);
        $coreRestrictionsMock = $this->createMock(Restrictions::class);

        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with(self::ENGINE_CODE)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('isGateway')
            ->willReturn(true);
        $this->coreRestrictionsProviderMock->expects($this->once())
            ->method('getRestrictions')
            ->willReturn($coreRestrictionsMock);
        $this->restrictionsFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $this->engineRestrictions])
            ->willReturn($restrictionsMock);

        $this->assertEquals($restrictionsMock, $this->restrictionsPool->getRestrictions(self::ENGINE_CODE));
    }

    public function testGetRestrictionsForGatewayEngineCaching()
    {
        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $restrictionsMock = $this->getMockForAbstractClass(RestrictionsInterface::class);
        $coreRestrictionsMock = $this->createMock(Restrictions::class);

        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with(self::ENGINE_CODE)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('isGateway')
            ->willReturn(true);
        $this->coreRestrictionsProviderMock->expects($this->once())
            ->method('getRestrictions')
            ->willReturn($coreRestrictionsMock);
        $this->restrictionsFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $this->engineRestrictions])
            ->willReturn($restrictionsMock);

        $this->restrictionsPool->getRestrictions(self::ENGINE_CODE);
        $this->restrictionsPool->getRestrictions(self::ENGINE_CODE);
    }

    public function testGetRestrictionsForLocalEngine()
    {
        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $restrictionsMock = $this->getMockForAbstractClass(RestrictionsInterface::class);
        $coreRestrictionsMock = $this->createMock(Restrictions::class);

        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with(self::ENGINE_CODE)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('isGateway')
            ->willReturn(false);
        $this->coreRestrictionsProviderMock->expects($this->once())
            ->method('getRestrictions')
            ->willReturn($coreRestrictionsMock);
        $coreRestrictionsMock->expects($this->once())
            ->method('getData')
            ->willReturn($this->coreRestrictions);
        $this->restrictionsFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => array_merge($this->coreRestrictions, $this->engineRestrictions)])
            ->willReturn($restrictionsMock);

        $this->assertEquals($restrictionsMock, $this->restrictionsPool->getRestrictions(self::ENGINE_CODE));
    }
}
