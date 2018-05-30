<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Controller\Paypalexpress;

use Aheadworks\Sarp\Api\SubscriptionsCartManagementInterface;
use Aheadworks\Sarp\Controller\Paypalexpress\ReturnAction;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ExpressCheckout;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Controller\Paypalexpress\ReturnAction
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReturnActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReturnAction
     */
    private $action;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var MessageManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var SubscriptionsCartManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartManagementMock;

    /**
     * @var Persistor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartPersistorMock;

    /**
     * @var ExpressCheckout|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(MessageManager::class);
        $this->cartManagementMock = $this->getMockForAbstractClass(SubscriptionsCartManagementInterface::class);
        $this->cartPersistorMock = $this->createMock(Persistor::class);
        $this->checkoutMock = $this->createMock(ExpressCheckout::class);
        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'messageManager' => $this->messageManagerMock
            ]
        );
        $this->action = $objectManager->getObject(
            ReturnAction::class,
            [
                'context' => $context,
                'cartManagement' => $this->cartManagementMock,
                'cartPersistor' => $this->cartPersistorMock,
                'checkout' => $this->checkoutMock
            ]
        );
    }

    public function testExecute()
    {
        $cartId = 1;
        $token = 'token_value';

        $resultRedirectMock = $this->createMock(Redirect::class);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('token')
            ->willReturn($token);
        $this->checkoutMock->expects($this->once())
            ->method('updateCart')
            ->with($token);
        $this->cartPersistorMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->cartManagementMock->expects($this->once())
            ->method('submit')
            ->with($cartId, ['token' => $token]);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('aw_sarp/checkout/success');

        $this->assertEquals($resultRedirectMock, $this->action->execute());
    }

    public function testExecuteLocalizedException()
    {
        $token = 'token_value';
        $exceptionMessage = 'Exception message';

        $resultRedirectMock = $this->createMock(Redirect::class);
        $exception = new LocalizedException(__($exceptionMessage));

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('token')
            ->willReturn($token);
        $this->checkoutMock->expects($this->once())
            ->method('updateCart')
            ->with($token)
            ->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, $exceptionMessage);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('aw_sarp/cart/index');

        $this->assertEquals($resultRedirectMock, $this->action->execute());
    }

    public function testExecuteException()
    {
        $token = 'token_value';
        $resultRedirectMock = $this->createMock(Redirect::class);
        $exception = new \Exception();

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('token')
            ->willReturn($token);
        $this->checkoutMock->expects($this->once())
            ->method('updateCart')
            ->with($token)
            ->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with(
                $exception,
                'We can\'t process Express Checkout recurring profile creation.'
            );
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('aw_sarp/cart/index');

        $this->assertEquals($resultRedirectMock, $this->action->execute());
    }
}
