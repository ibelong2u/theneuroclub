<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterfaceFactory;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Model\Session as SarpSession;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Persistor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PersistorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Persistor
     */
    private $persistor;

    /**
     * @var SarpSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sarpSessionMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var SubscriptionsCartInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionsCartFactoryMock;

    /**
     * @var SubscriptionsCartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionsCartRepositoryMock;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var Copy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectCopyServiceMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->sarpSessionMock = $this->createMock(SarpSession::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->subscriptionsCartFactoryMock = $this->createMock(SubscriptionsCartInterfaceFactory::class);
        $this->subscriptionsCartRepositoryMock = $this->getMockForAbstractClass(
            SubscriptionsCartRepositoryInterface::class
        );
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->customerRepositoryMock = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->objectCopyServiceMock = $this->createMock(Copy::class);
        $this->persistor = $objectManager->getObject(
            Persistor::class,
            [
                'sarpSession' => $this->sarpSessionMock,
                'storeManager' => $this->storeManagerMock,
                'subscriptionsCartFactory' => $this->subscriptionsCartFactoryMock,
                'subscriptionsCartRepository' => $this->subscriptionsCartRepositoryMock,
                'customerSession' => $this->customerSessionMock,
                'customerRepository' => $this->customerRepositoryMock,
                'objectCopyService' => $this->objectCopyServiceMock
            ]
        );
    }

    public function testGetSubscriptionCartNewForGuest()
    {
        $storeId = 1;
        $websiteId = 2;

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);

        $this->storeManagerMock->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->sarpSessionMock->expects($this->once())
            ->method('getCartId')
            ->with($websiteId)
            ->willReturn(null);
        $this->customerSessionMock->expects($this->exactly(2))
            ->method('isLoggedIn')
            ->willReturn(false);
        $this->subscriptionsCartFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($cartMock);
        $cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn(null);
        $cartMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);

        $this->assertEquals($cartMock, $this->persistor->getSubscriptionCart());
        $this->persistor->getSubscriptionCart();
    }

    public function testGetSubscriptionCartStoredForGuest()
    {
        $cartId = 1;
        $storeId = 2;
        $websiteId = 3;
        $currencyCode = 'USD';

        $storeMock = $this->createMock(Store::class);
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);

        $this->storeManagerMock->expects($this->exactly(4))
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $storeMock->expects($this->exactly(2))
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->sarpSessionMock->expects($this->exactly(2))
            ->method('getCartId')
            ->with($websiteId)
            ->willReturn($cartId);
        $this->subscriptionsCartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($cartMock);
        $cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $cartMock->expects($this->once())
            ->method('getCartCurrencyCode')
            ->willReturn($currencyCode);
        $storeMock->expects($this->once())
            ->method('getCurrentCurrencyCode')
            ->willReturn($currencyCode);
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);
        $cartMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);

        $this->assertEquals($cartMock, $this->persistor->getSubscriptionCart());
        $this->persistor->getSubscriptionCart();
    }

    public function testGetSubscriptionCartNewForCustomer()
    {
        $storeId = 1;
        $websiteId = 2;
        $customerId = 3;

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->storeManagerMock->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->sarpSessionMock->expects($this->once())
            ->method('getCartId')
            ->with($websiteId)
            ->willReturn(null);
        $this->customerSessionMock->expects($this->exactly(2))
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->exactly(2))
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->subscriptionsCartRepositoryMock->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($customerId)
            ->willThrowException(NoSuchEntityException::singleField('customerId', $customerId));
        $this->subscriptionsCartFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($cartMock);
        $cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn(null);
        $cartMock->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);
        $this->objectCopyServiceMock->expects($this->once())
            ->method('copyFieldsetToTarget')
            ->with(
                'aw_sarp_customer',
                'to_cart',
                $customerMock,
                $cartMock
            );
        $cartMock->expects($this->once())
            ->method('setCustomerIsGuest')
            ->with(false);
        $cartMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);

        $this->assertEquals($cartMock, $this->persistor->getSubscriptionCart());
        $this->persistor->getSubscriptionCart();
    }

    public function testGetSubscriptionCartStoredForCustomer()
    {
        $cartId = 1;
        $storeId = 2;
        $websiteId = 3;
        $customerId = 4;
        $currencyCode = 'USD';

        $storeMock = $this->createMock(Store::class);
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->storeManagerMock->expects($this->exactly(4))
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $storeMock->expects($this->exactly(2))
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->sarpSessionMock->expects($this->exactly(2))
            ->method('getCartId')
            ->with($websiteId)
            ->willReturn($cartId);
        $this->subscriptionsCartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($cartMock);
        $cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $cartMock->expects($this->once())
            ->method('getCartCurrencyCode')
            ->willReturn($currencyCode);
        $storeMock->expects($this->once())
            ->method('getCurrentCurrencyCode')
            ->willReturn($currencyCode);
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);
        $this->objectCopyServiceMock->expects($this->once())
            ->method('copyFieldsetToTarget')
            ->with(
                'aw_sarp_customer',
                'to_cart',
                $customerMock,
                $cartMock
            );
        $cartMock->expects($this->once())
            ->method('setCustomerIsGuest')
            ->with(false);
        $cartMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);

        $this->assertEquals($cartMock, $this->persistor->getSubscriptionCart());
        $this->persistor->getSubscriptionCart();
    }

    public function testGetSubscriptionCartLoadForCustomer()
    {
        $cartId = 1;
        $storeId = 2;
        $websiteId = 3;
        $customerId = 4;

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->storeManagerMock->expects($this->exactly(3))
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $storeMock->expects($this->exactly(2))
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->sarpSessionMock->expects($this->once())
            ->method('getCartId')
            ->with($websiteId)
            ->willReturn(null);
        $this->customerSessionMock->expects($this->exactly(2))
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->exactly(2))
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->subscriptionsCartRepositoryMock->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($customerId)
            ->willReturn($cartMock);
        $cartMock->expects($this->exactly(2))
            ->method('getCartId')
            ->willReturn($cartId);
        $this->sarpSessionMock->expects($this->once())
            ->method('setCartId')
            ->with($cartId, $websiteId);
        $cartMock->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);
        $this->objectCopyServiceMock->expects($this->once())
            ->method('copyFieldsetToTarget')
            ->with(
                'aw_sarp_customer',
                'to_cart',
                $customerMock,
                $cartMock
            );
        $cartMock->expects($this->once())
            ->method('setCustomerIsGuest')
            ->with(false);
        $cartMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);

        $this->assertEquals($cartMock, $this->persistor->getSubscriptionCart());
        $this->persistor->getSubscriptionCart();
    }

    public function testGetCartId()
    {
        $cartId = 1;
        $websiteId = 2;

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->sarpSessionMock->expects($this->once())
            ->method('getCartId')
            ->with($websiteId)
            ->willReturn($cartId);

        $this->assertEquals($cartId, $this->persistor->getCartId());
    }

    public function testSetCartId()
    {
        $cartId = 1;
        $websiteId = 2;

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->sarpSessionMock->expects($this->once())
            ->method('setCartId')
            ->with($cartId, $websiteId);

        $this->persistor->setCartId($cartId);
    }

    public function testClear()
    {
        $websiteId = 1;

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->sarpSessionMock->expects($this->once())
            ->method('setCartId')
            ->with(null, $websiteId);
        $this->sarpSessionMock->expects($this->exactly(2))
            ->method('__call')
            ->withConsecutive(
                ['setLastSuccessCartId', [null]],
                ['setLastProfileId', [null]]
            )
            ->willReturnSelf();

        $this->persistor->clear();
    }
}
