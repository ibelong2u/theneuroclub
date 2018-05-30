<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Observer;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterfaceFactory;
use Aheadworks\Sarp\Api\SubscriptionsCartManagementInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Aheadworks\Sarp\Observer\LoadCustomerCartObserver;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Observer\LoadCustomerCartObserver
 */
class LoadCustomerCartObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LoadCustomerCartObserver
     */
    private $observer;

    /**
     * @var Persistor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartPersistorMock;

    /**
     * @var SubscriptionsCartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var SubscriptionsCartInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartFactoryMock;

    /**
     * @var SubscriptionsCartManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartManagementMock;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $observerMock;

    /**
     * @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerCartMock;

    /**
     * @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currentCartMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->cartPersistorMock = $this->createMock(Persistor::class);
        $this->cartRepositoryMock = $this->getMockForAbstractClass(SubscriptionsCartRepositoryInterface::class);
        $this->cartFactoryMock = $this->createMock(SubscriptionsCartInterfaceFactory::class);
        $this->cartManagementMock = $this->getMockForAbstractClass(SubscriptionsCartManagementInterface::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);

        $this->observerMock = $this->createMock(Observer::class);
        $this->customerCartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $this->currentCartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);

        $this->observer = $objectManager->getObject(
            LoadCustomerCartObserver::class,
            [
                'cartPersistor' => $this->cartPersistorMock,
                'cartRepository' => $this->cartRepositoryMock,
                'cartFactory' => $this->cartFactoryMock,
                'cartManagement' => $this->cartManagementMock,
                'customerSession' => $this->customerSessionMock
            ]
        );
    }

    public function testExecute()
    {
        $customerId = 1;
        $customerCartId = 2;
        $currentCartId = 3;

        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->cartRepositoryMock->expects($this->once())
            ->method('getForCustomer')
            ->with($customerId)
            ->willReturn($this->customerCartMock);
        $this->cartPersistorMock->expects($this->once())
            ->method('getSubscriptionCart')
            ->willReturn($this->currentCartMock);
        $this->customerCartMock->expects($this->exactly(3))
            ->method('getCartId')
            ->willReturn($customerCartId);
        $this->cartPersistorMock->expects($this->exactly(2))
            ->method('getCartId')
            ->willReturn($currentCartId);
        $this->cartManagementMock->expects($this->once())
            ->method('merge')
            ->with($this->customerCartMock, $this->currentCartMock);
        $this->cartPersistorMock->expects($this->once())
            ->method('setCartId')
            ->with($customerCartId);
        $this->currentCartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($currentCartId);
        $this->cartRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($this->currentCartMock);

        $this->observer->execute($this->observerMock);
    }

    public function testExecuteNoCurrentCart()
    {
        $customerId = 1;
        $customerCartId = 2;
        $currentCartId = null;

        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->cartRepositoryMock->expects($this->once())
            ->method('getForCustomer')
            ->with($customerId)
            ->willReturn($this->customerCartMock);
        $this->cartPersistorMock->expects($this->once())
            ->method('getSubscriptionCart')
            ->willReturn($this->currentCartMock);
        $this->customerCartMock->expects($this->exactly(3))
            ->method('getCartId')
            ->willReturn($customerCartId);
        $this->cartPersistorMock->expects($this->exactly(2))
            ->method('getCartId')
            ->willReturn($currentCartId);
        $this->cartManagementMock->expects($this->never())
            ->method('merge')
            ->with($this->customerCartMock, $this->currentCartMock);
        $this->cartPersistorMock->expects($this->once())
            ->method('setCartId')
            ->with($customerCartId);
        $this->currentCartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($currentCartId);
        $this->cartRepositoryMock->expects($this->never())
            ->method('delete')
            ->with($this->currentCartMock);

        $this->observer->execute($this->observerMock);
    }

    public function testExecuteNoCustomerCart()
    {
        $customerId = 1;

        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->cartRepositoryMock->expects($this->once())
            ->method('getForCustomer')
            ->with($customerId)
            ->willThrowException(NoSuchEntityException::singleField('customerId', $customerId));
        $this->cartFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->customerCartMock);
        $this->cartPersistorMock->expects($this->once())
            ->method('getSubscriptionCart')
            ->willReturn($this->currentCartMock);
        $this->customerCartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn(null);
        $this->currentCartMock->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId);
        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->currentCartMock);

        $this->observer->execute($this->observerMock);
    }
}
