<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Controller\Product;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterfaceFactory;
use Aheadworks\Sarp\Api\SubscriptionsCartManagementInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Aheadworks\Sarp\Controller\Product\Subscribe;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;

/**
 * Test for \Aheadworks\Sarp\Controller\Product\Subscribe
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscribeTest extends \PHPUnit\Framework\TestCase
{
    const PRODUCT_ID = 1;
    const QTY = 2;

    /**
     * @var Subscribe
     */
    private $action;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var MessageManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var SubscriptionsCartManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartManagementMock;

    /**
     * @var Persistor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartPersistorMock;

    /**
     * @var SubscriptionsCartItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemFactoryMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonMock;

    /**
     * @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartMock;

    /**
     * @var SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemMock;

    /**
     * @var array
     */
    private $buyRequest = [
        'product' => self::PRODUCT_ID,
        'product_id' => self::PRODUCT_ID,
        'qty' => self::QTY
    ];

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(MessageManager::class);
        $this->urlMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->cartManagementMock = $this->getMockForAbstractClass(SubscriptionsCartManagementInterface::class);
        $this->cartPersistorMock = $this->createMock(Persistor::class);
        $this->itemFactoryMock = $this->createMock(SubscriptionsCartItemInterfaceFactory::class);

        $this->resultJsonMock = $this->createMock(Json::class);
        $this->cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $this->itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultJsonMock);
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($this->buyRequest);
        $this->cartPersistorMock->expects($this->once())
            ->method('getSubscriptionCart')
            ->willReturn($this->cartMock);
        $this->itemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->itemMock);
        $this->itemMock->expects($this->once())
            ->method('setProductId')
            ->with(self::PRODUCT_ID)
            ->willReturnSelf();
        $this->itemMock->expects($this->once())
            ->method('setBuyRequest')
            ->with(serialize($this->buyRequest));
        $this->itemMock->expects($this->once())
            ->method('setQty')
            ->with(self::QTY);

        $context = $objectManager->getObject(
            Context::class,
            [
                'resultFactory' => $this->resultFactoryMock,
                'request' => $this->requestMock,
                'messageManager' => $this->messageManagerMock,
                'url' => $this->urlMock
            ]
        );
        $this->action = $objectManager->getObject(
            Subscribe::class,
            [
                'context' => $context,
                'cartManagement' =>$this->cartManagementMock,
                'cartPersistor' => $this->cartPersistorMock,
                'itemFactory' => $this->itemFactoryMock
            ]
        );
    }

    public function testExecute()
    {
        $cartId = 3;
        $itemName = 'Item name';
        $cartUrl = 'http://localhost/aw_sarp/cart/index';

        $this->cartManagementMock->expects($this->once())
            ->method('add')
            ->with($this->cartMock, $this->itemMock)
            ->willReturn($this->itemMock);
        $this->cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->itemMock->expects($this->once())
            ->method('getName')
            ->willReturn($itemName);
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('You added Item name to subscription cart.');
        $this->urlMock->expects($this->once())
            ->method('getUrl')
            ->with('aw_sarp/cart/index')
            ->willReturn($cartUrl);
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with(['redirectUrl' => $cartUrl])
            ->willReturnSelf();

        $this->assertEquals($this->resultJsonMock, $this->action->execute());
    }

    public function testExecuteLocalizedException()
    {
        $exceptionMessage = 'Exception message';

        $this->cartManagementMock->expects($this->once())
            ->method('add')
            ->with($this->cartMock, $this->itemMock)
            ->willThrowException(new LocalizedException(__($exceptionMessage)));
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($exceptionMessage);
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with([])
            ->willReturnSelf();

        $this->assertEquals($this->resultJsonMock, $this->action->execute());
    }

    public function testExecuteException()
    {
        $exception = new \Exception();

        $this->cartManagementMock->expects($this->once())
            ->method('add')
            ->with($this->cartMock, $this->itemMock)
            ->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with(
                $exception,
                'We can\'t add this item to subscription cart right now.'
            );
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with([])
            ->willReturnSelf();

        $this->assertEquals($this->resultJsonMock, $this->action->execute());
    }
}
