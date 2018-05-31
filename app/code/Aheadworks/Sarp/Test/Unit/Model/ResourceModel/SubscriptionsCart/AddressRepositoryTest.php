<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\ResourceModel\SubscriptionsCart;

use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterfaceFactory;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressSearchResultsInterface as SearchResults;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressSearchResultsInterfaceFactory as SearchResultsFactory;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart\AddressRepository;
use Aheadworks\Sarp\Model\SubscriptionsCart\TotalsCollector;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart\AddressRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AddressRepository
     */
    private $repository;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var SubscriptionsCartAddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressFactoryMock;

    /**
     * @var SearchResultsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactoryMock;

    /**
     * @var SubscriptionsCartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var TotalsCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsCollectorMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->addressFactoryMock = $this->createMock(SubscriptionsCartAddressInterfaceFactory::class);
        $this->searchResultsFactoryMock = $this->createMock(SearchResultsFactory::class);
        $this->cartRepositoryMock = $this->getMockForAbstractClass(SubscriptionsCartRepositoryInterface::class);
        $this->totalsCollectorMock = $this->createMock(TotalsCollector::class);
        $this->repository = $objectManager->getObject(
            AddressRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'addressFactory' => $this->addressFactoryMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'cartRepository' => $this->cartRepositoryMock,
                'totalsCollector' => $this->totalsCollectorMock
            ]
        );
    }

    public function testSave()
    {
        $cartId = 1;
        $addressId = 2;

        /** @var SubscriptionsCartAddressInterface|\PHPUnit_Framework_MockObject_MockObject $addressMock */
        $addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $addressMock->expects($this->exactly(2))
            ->method('getCartId')
            ->willReturn($cartId);
        $addressMock->expects($this->exactly(3))
            ->method('getAddressId')
            ->willReturn($addressId);
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);

        $this->entityManagerMock->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive([$addressMock], [$cartMock]);
        $this->cartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($cartMock);
        $this->totalsCollectorMock->expects($this->once())
            ->method('collect')
            ->with($cartMock);
        $this->addressFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($addressMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($addressMock, $addressId);

        $this->assertEquals($addressMock, $this->repository->save($addressMock));
    }

    public function testGet()
    {
        $addressId = 1;

        $addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $this->addressFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($addressMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($addressMock, $addressId);
        $addressMock->expects($this->once())
            ->method('getAddressId')
            ->willReturn($addressId);

        $this->repository->get($addressId);
        $this->assertEquals($addressMock, $this->repository->get($addressId));
    }

    public function testGetList()
    {
        $cartId = 1;

        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $searchResultsMock = $this->getMockForAbstractClass(SearchResults::class);
        $addresses = [
            $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class),
            $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class)
        ];

        $this->cartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($cartMock);
        $this->searchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);
        $cartMock->expects($this->exactly(2))
            ->method('getAddresses')
            ->willReturn($addresses);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with($addresses)
            ->willReturnSelf();
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with(2)
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->repository->getList($cartId));
    }
}
