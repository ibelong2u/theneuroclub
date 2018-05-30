<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\ResourceModel;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterfaceFactory;
use Aheadworks\Sarp\Model\SubscriptionsCart\TotalsCollector;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart as SubscriptionsCartResource;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCartRepository;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test for \Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCartRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriptionsCartRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubscriptionsCartRepository
     */
    private $repository;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var SubscriptionsCartInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartFactoryMock;

    /**
     * @var SubscriptionsCartResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartResourceMock;

    /**
     * @var TotalsCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsCollectorMock;

    /**
     * @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->cartFactoryMock = $this->createMock(SubscriptionsCartInterfaceFactory::class);
        $this->cartResourceMock = $this->createMock(SubscriptionsCartResource::class);
        $this->totalsCollectorMock = $this->createMock(TotalsCollector::class);

        $this->cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);

        $this->repository = $objectManager->getObject(
            SubscriptionsCartRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'storeManager' => $this->storeManagerMock,
                'subscriptionsCartFactory' => $this->cartFactoryMock,
                'subscriptionsCartResource' => $this->cartResourceMock,
                'totalsCollector' => $this->totalsCollectorMock
            ]
        );
    }

    /**
     * Set up mocks for getSharedStoreIds() method
     *
     * @param int $storeId
     */
    private function setUpSharedStoreIds($storeId)
    {
        $websiteId = 1;

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getStores')
            ->willReturn([$storeMock]);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
    }

    public function testSave()
    {
        $cartId = 1;
        $storeId = 2;

        $this->totalsCollectorMock->expects($this->once())
            ->method('collect')
            ->with($this->cartMock);
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($this->cartMock);
        $this->cartMock->expects($this->exactly(3))
            ->method('getCartId')
            ->willReturn($cartId);
        $this->cartFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->cartMock);
        $this->setUpSharedStoreIds($storeId);
        $this->cartMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->cartMock, $cartId, ['storeIds' => [$storeId]]);

        $this->assertEquals($this->cartMock, $this->repository->save($this->cartMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Exception message
     */
    public function testSaveException()
    {
        $exceptionMessage = 'Exception message';

        $this->totalsCollectorMock->expects($this->once())
            ->method('collect')
            ->with($this->cartMock);
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($this->cartMock)
            ->willThrowException(new \Exception($exceptionMessage));

        $this->repository->save($this->cartMock);
    }

    public function testGet()
    {
        $cartId = 1;
        $storeId = 2;

        $this->cartFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->cartMock);
        $this->setUpSharedStoreIds($storeId);
        $this->cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->cartMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->cartMock, $cartId, ['storeIds' => [$storeId]]);

        $this->repository->get($cartId);
        $this->assertEquals($this->cartMock, $this->repository->get($cartId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 1
     */
    public function testGetException()
    {
        $cartId = 1;
        $storeId = 2;

        $this->cartFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->cartMock);
        $this->setUpSharedStoreIds($storeId);
        $this->cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn(null);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->cartMock, $cartId, ['storeIds' => [$storeId]]);

        $this->repository->get($cartId);
    }

    public function testGetActive()
    {
        $cartId = 1;
        $storeId = 2;

        $this->cartFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->cartMock);
        $this->setUpSharedStoreIds($storeId);
        $this->cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->cartMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->cartMock, $cartId, ['storeIds' => [$storeId]]);
        $this->cartMock->expects($this->exactly(2))
            ->method('getIsActive')
            ->willReturn(true);

        $this->repository->getActive($cartId);
        $this->assertEquals($this->cartMock, $this->repository->getActive($cartId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 1
     */
    public function testGetActiveException()
    {
        $cartId = 1;
        $storeId = 2;

        $this->cartFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->cartMock);
        $this->setUpSharedStoreIds($storeId);
        $this->cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->cartMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->cartMock, $cartId, ['storeIds' => [$storeId]]);
        $this->cartMock->expects($this->once())
            ->method('getIsActive')
            ->willReturn(false);

        $this->repository->getActive($cartId);
    }

    public function testGetForCustomer()
    {
        $customerId = 1;
        $cartId = 2;
        $storeId = 3;

        $this->cartResourceMock->expects($this->once())
            ->method('getCartIdByCustomerId')
            ->with($customerId)
            ->willReturn($cartId);
        $this->cartFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->cartMock);
        $this->setUpSharedStoreIds($storeId);
        $this->cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->cartMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->cartMock, $cartId, ['storeIds' => [$storeId]]);

        $this->repository->getForCustomer($customerId);
        $this->assertEquals($this->cartMock, $this->repository->getForCustomer($customerId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customerId = 1
     */
    public function testGetForCustomerException()
    {
        $customerId = 1;

        $this->cartResourceMock->expects($this->once())
            ->method('getCartIdByCustomerId')
            ->with($customerId)
            ->willReturn(null);

        $this->repository->getForCustomer($customerId);
    }

    public function testGetActiveForCustomer()
    {
        $customerId = 1;
        $cartId = 2;
        $storeId = 3;

        $this->cartResourceMock->expects($this->once())
            ->method('getCartIdByCustomerId')
            ->with($customerId)
            ->willReturn($cartId);
        $this->cartFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->cartMock);
        $this->setUpSharedStoreIds($storeId);
        $this->cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->cartMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->cartMock, $cartId, ['storeIds' => [$storeId]]);
        $this->cartMock->expects($this->exactly(2))
            ->method('getIsActive')
            ->willReturn(true);

        $this->repository->getActiveForCustomer($customerId);
        $this->assertEquals(
            $this->cartMock,
            $this->repository->getActiveForCustomer($customerId)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customerId = 1
     */
    public function testGetActiveForCustomerException()
    {
        $customerId = 1;
        $cartId = 2;
        $storeId = 3;

        $this->cartResourceMock->expects($this->once())
            ->method('getCartIdByCustomerId')
            ->with($customerId)
            ->willReturn($cartId);
        $this->cartFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->cartMock);
        $this->setUpSharedStoreIds($storeId);
        $this->cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->cartMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->cartMock, $cartId, ['storeIds' => [$storeId]]);
        $this->cartMock->expects($this->once())
            ->method('getIsActive')
            ->willReturn(false);

        $this->repository->getActiveForCustomer($customerId);
    }

    public function testDelete()
    {
        $cartId = 1;
        $customerId = 2;

        $this->cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->cartMock->expects($this->exactly(2))
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($this->cartMock);

        $this->repository->delete($this->cartMock);
    }
}
