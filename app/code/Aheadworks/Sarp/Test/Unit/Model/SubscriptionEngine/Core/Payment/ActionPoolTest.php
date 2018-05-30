<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Core\Payment;

use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionPool;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionPool
 */
class ActionPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ActionPool
     */
    private $actionPool;

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
        $this->actionPool = $objectManager->getObject(
            ActionPool::class,
            [
                'objectManager' => $this->objectManagerMock,
                'engineMetadataPool' => $this->engineMetadataPoolMock
            ]
        );
    }

    public function testGetAction()
    {
        $engineCode = 'engine_code';
        $methodCode = 'method_code';
        $actionClassName = 'PaymentMethod';
        $enginePaymentMethods = [
            $methodCode => [
                'payment_action' => $actionClassName
            ]
        ];

        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $actionInstanceMock = $this->getMockForAbstractClass(ActionInterface::class);

        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($engineCode)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getPaymentMethods')
            ->willReturn($enginePaymentMethods);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($actionClassName)
            ->willReturn($actionInstanceMock);

        $this->assertSame($actionInstanceMock, $this->actionPool->getAction($engineCode, $methodCode));
    }

    public function testGetActionCaching()
    {
        $engineCode = 'engine_code';
        $methodCode = 'method_code';
        $actionClassName = 'PaymentMethod';
        $enginePaymentMethods = [
            $methodCode => [
                'payment_action' => $actionClassName
            ]
        ];

        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $actionInstanceMock = $this->getMockForAbstractClass(ActionInterface::class);

        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($engineCode)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getPaymentMethods')
            ->willReturn($enginePaymentMethods);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($actionClassName)
            ->willReturn($actionInstanceMock);

        $this->actionPool->getAction($engineCode, $methodCode);
        $this->actionPool->getAction($engineCode, $methodCode);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown payment method: method_code in engine: engine_code requested
     */
    public function testGetActionUnknownPaymentMethodException()
    {
        $engineCode = 'engine_code';
        $methodCode = 'method_code';
        $enginePaymentMethods = [
            'configured_method_code' => [
                'payment_action' => 'PaymentMethod'
            ]
        ];

        $metadataMock = $this->getMockForAbstractClass(EngineMetadataInterface::class);
        $this->engineMetadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($engineCode)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getPaymentMethods')
            ->willReturn($enginePaymentMethods);

        $this->actionPool->getAction($engineCode, $methodCode);
    }
}
