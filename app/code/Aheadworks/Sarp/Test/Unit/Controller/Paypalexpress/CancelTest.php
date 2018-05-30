<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Controller\Paypalexpress;

use Aheadworks\Sarp\Controller\Paypalexpress\Cancel as CancelAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Controller\Paypalexpress\Cancel
 */
class CancelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CancelAction
     */
    private $action;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var MessageManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(MessageManager::class);
        $context = $objectManager->getObject(
            Context::class,
            [
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'messageManager' => $this->messageManagerMock
            ]
        );
        $this->action = $objectManager->getObject(
            CancelAction::class,
            ['context' => $context]
        );
    }

    public function testExecute()
    {
        $resultRedirectMock = $this->createMock(Redirect::class);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('Express Checkout has been canceled.');
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('aw_sarp/cart/index');

        $this->assertEquals($resultRedirectMock, $this->action->execute());
    }
}
