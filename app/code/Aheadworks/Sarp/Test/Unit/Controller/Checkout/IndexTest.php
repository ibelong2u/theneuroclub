<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Controller\Checkout;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Controller\Checkout\Index;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Aheadworks\Sarp\Model\SubscriptionsCart\CheckoutValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Test for \Aheadworks\Sarp\Controller\Checkout\Index
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Index
     */
    private $action;

    /**
     * @var PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Persistor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartPersistorMock;

    /**
     * @var CheckoutValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutValidatorMock;

    /**
     * @var MessageManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->cartPersistorMock = $this->createMock(Persistor::class);
        $this->checkoutValidatorMock = $this->createMock(CheckoutValidator::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(MessageManager::class);
        $context = $objectManager->getObject(
            Context::class,
            [
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'messageManager' => $this->messageManagerMock
            ]
        );
        $this->action = $objectManager->getObject(
            Index::class,
            [
                'context' => $context,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'cartPersistor' => $this->cartPersistorMock,
                'checkoutValidator' => $this->checkoutValidatorMock
            ]
        );
    }

    public function testExecute()
    {
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $resultPageMock = $this->createMock(Page::class);
        $pageConfigMock = $this->createMock(PageConfig::class);
        $titleMock = $this->createMock(Title::class);

        $this->cartPersistorMock->expects($this->once())
            ->method('getSubscriptionCart')
            ->willReturn($cartMock);
        $this->checkoutValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($cartMock)
            ->willReturn(true);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);
        $resultPageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($pageConfigMock);
        $pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($titleMock);
        $titleMock->expects($this->once())
            ->method('set')
            ->with('Subscription Checkout');

        $this->assertEquals($resultPageMock, $this->action->execute());
    }

    public function testExecuteInvalid()
    {
        $message = 'Subscription cart is empty.';

        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $resultRedirectMock = $this->createMock(Redirect::class);

        $this->cartPersistorMock->expects($this->once())
            ->method('getSubscriptionCart')
            ->willReturn($cartMock);
        $this->checkoutValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($cartMock)
            ->willReturn(false);
        $this->checkoutValidatorMock->expects($this->once())
            ->method('getMessages')
            ->willReturn([$message]);
        $this->messageManagerMock->expects($this->once())
            ->method('addNoticeMessage')
            ->with($message);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('aw_sarp/cart/index');

        $this->assertEquals($resultRedirectMock, $this->action->execute());
    }
}
