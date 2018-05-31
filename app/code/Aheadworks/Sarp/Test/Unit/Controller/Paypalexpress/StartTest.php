<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Controller\Paypalexpress;

use Aheadworks\Sarp\Controller\Paypalexpress\Start as StartAction;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ExpressCheckout;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Controller\Paypalexpress\Start
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StartAction
     */
    private $action;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var MessageManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var ExpressCheckout|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutMock;

    /**
     * @var Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->responseMock = $this->createMock(Http::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(MessageManager::class);
        $this->checkoutMock = $this->createMock(ExpressCheckout::class);
        $this->urlMock = $this->createMock(Url::class);
        $context = $objectManager->getObject(
            Context::class,
            [
                'response' => $this->responseMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'messageManager' => $this->messageManagerMock
            ]
        );
        $this->action = $objectManager->getObject(
            StartAction::class,
            [
                'context' => $context,
                'checkout' => $this->checkoutMock,
                'url' => $this->urlMock
            ]
        );
    }

    public function testExecute()
    {
        $token = 'token_value';
        $paypalStartUrl = 'https://www.paypal.com';

        $this->checkoutMock->expects($this->once())
            ->method('start')
            ->willReturn($token);
        $this->urlMock->expects($this->once())
            ->method('getPaypalStartUrl')
            ->with($token)
            ->willReturn($paypalStartUrl);
        $this->responseMock->expects($this->once())
            ->method('setRedirect')
            ->with($paypalStartUrl);

        $this->action->execute();
    }

    public function testExecuteLocalizedException()
    {
        $exceptionMessage = 'Exception message';

        $resultRedirectMock = $this->createMock(Redirect::class);
        $exception = new LocalizedException(__($exceptionMessage));

        $this->checkoutMock->expects($this->once())
            ->method('start')
            ->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, $exceptionMessage);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('aw_sarp/cart/index');

        $this->assertEquals($resultRedirectMock, $this->action->execute());
    }

    public function testExecuteException()
    {
        $resultRedirectMock = $this->createMock(Redirect::class);
        $exception = new \Exception();

        $this->checkoutMock->expects($this->once())
            ->method('start')
            ->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, 'We can\'t start Express Checkout.');
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('aw_sarp/cart/index');

        $this->assertEquals($resultRedirectMock, $this->action->execute());
    }
}
