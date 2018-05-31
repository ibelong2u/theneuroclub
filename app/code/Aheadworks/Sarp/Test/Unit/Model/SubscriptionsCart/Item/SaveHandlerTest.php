<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart\Item;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\BuyRequestProcessor;
use Aheadworks\Sarp\Model\SubscriptionsCart\Item\SaveHandler;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Item\SaveHandler
 */
class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var BuyRequestProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $buyRequestProcessorMock;

    /**
     * @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartMock;

    /**
     * @var SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->buyRequestProcessorMock = $this->createMock(BuyRequestProcessor::class);

        $this->cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $this->itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->saveHandler = $objectManager->getObject(
            SaveHandler::class,
            [
                'entityManager' => $this->entityManagerMock,
                'buyRequestProcessor' => $this->buyRequestProcessorMock
            ]
        );
    }

    public function testExecuteNew()
    {
        $cartId = 1;

        $this->cartMock->expects($this->once())
            ->method('getInnerItems')
            ->willReturn([$this->itemMock]);
        $this->itemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn(null);
        $this->itemMock->expects($this->once())
            ->method('getIsDeleted')
            ->willReturn(null);
        $this->cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->itemMock->expects($this->once())
            ->method('setCartId')
            ->with($cartId);
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($this->itemMock);

        $this->assertEquals($this->cartMock, $this->saveHandler->execute($this->cartMock));
    }

    public function testExecuteExisting()
    {
        $cartId = 1;
        $itemId = 2;
        $buyRequest = 'a:1:{s:10:"product_id";s:1:"1";}';
        $qty = 1;

        $this->cartMock->expects($this->once())
            ->method('getInnerItems')
            ->willReturn([$this->itemMock]);
        $this->itemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn($itemId);
        $this->itemMock->expects($this->once())
            ->method('getIsDeleted')
            ->willReturn(null);
        $this->itemMock->expects($this->once())
            ->method('getBuyRequest')
            ->willReturn($buyRequest);
        $this->itemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($qty);
        $this->buyRequestProcessorMock->expects($this->once())
            ->method('setQty')
            ->with($buyRequest, $qty)
            ->willReturnArgument(0);
        $this->cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->itemMock->expects($this->once())
            ->method('setBuyRequest')
            ->with($buyRequest)
            ->willReturnSelf();
        $this->itemMock->expects($this->once())
            ->method('setCartId')
            ->with($cartId);
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($this->itemMock);

        $this->assertEquals($this->cartMock, $this->saveHandler->execute($this->cartMock));
    }

    public function testExecuteDeleted()
    {
        $itemId = 1;

        $this->cartMock->expects($this->once())
            ->method('getInnerItems')
            ->willReturn([$this->itemMock]);
        $this->itemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn($itemId);
        $this->itemMock->expects($this->once())
            ->method('getIsDeleted')
            ->willReturn(true);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($this->itemMock);

        $this->assertEquals($this->cartMock, $this->saveHandler->execute($this->cartMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Exception message
     */
    public function testExecuteException()
    {
        $cartId = 1;

        $this->cartMock->expects($this->once())
            ->method('getInnerItems')
            ->willReturn([$this->itemMock]);
        $this->itemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn(null);
        $this->itemMock->expects($this->once())
            ->method('getIsDeleted')
            ->willReturn(null);
        $this->cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->itemMock->expects($this->once())
            ->method('setCartId')
            ->with($cartId);
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($this->itemMock)
            ->willThrowException(new \Exception('Exception message'));

        $this->saveHandler->execute($this->cartMock);
    }
}
