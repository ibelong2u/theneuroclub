<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\ResourceModel\SubscriptionsCart;

use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterfaceFactory;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemSearchResultsInterface as SearchResults;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemSearchResultsInterfaceFactory as SearchResultsFactory;
use Aheadworks\Sarp\Model\SubscriptionsCart\ItemsComparator;
use Magento\Framework\Api\DataObjectHelper;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart\ItemRepository;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart\ItemRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ItemRepository
     */
    private $repository;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var SubscriptionsCartItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemFactoryMock;

    /**
     * @var SearchResultsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactoryMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var SubscriptionsCartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var ItemsComparator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemComparatorMock;

    /**
     * @var SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemMock;

    /**
     * @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->itemFactoryMock = $this->createMock(SubscriptionsCartItemInterfaceFactory::class);
        $this->searchResultsFactoryMock = $this->createMock(SearchResultsFactory::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->cartRepositoryMock = $this->getMockForAbstractClass(SubscriptionsCartRepositoryInterface::class);
        $this->itemComparatorMock = $this->createMock(ItemsComparator::class);
        $this->itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $this->cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);

        $this->repository = $objectManager->getObject(
            ItemRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'itemFactory' => $this->itemFactoryMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'cartRepository' => $this->cartRepositoryMock,
                'itemComparator' => $this->itemComparatorMock
            ]
        );
    }

    public function testSave()
    {
        $cartId = 1;

        $cartItemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->itemMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->cartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->willReturn($this->cartMock);
        $this->cartMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$cartItemMock]);
        $this->itemComparatorMock->expects($this->once())
            ->method('isEquals')
            ->willReturn(true);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('mergeDataObjects')
            ->with(SubscriptionsCartItemInterface::class, $cartItemMock, $this->itemMock);
        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->cartMock);

        $this->assertSame($this->cartMock, $this->repository->save($this->itemMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Exception message
     */
    public function testSaveException()
    {
        $cartId = 1;
        $exceptionMessage = 'Exception message';

        $cartItemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->itemMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->cartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->willReturn($this->cartMock);
        $this->cartMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$cartItemMock]);
        $this->itemComparatorMock->expects($this->once())
            ->method('isEquals')
            ->willReturn(true);
        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->cartMock)
            ->willThrowException(new \Exception($exceptionMessage));

        $this->repository->save($this->itemMock);
    }

    public function testGetList()
    {
        $cartId = 1;

        $searchResultsMock = $this->getMockForAbstractClass(SearchResults::class);

        $this->cartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->cartMock);
        $this->searchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);
        $this->cartMock->expects($this->exactly(2))
            ->method('getItems')
            ->willReturn([$this->itemMock]);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$this->itemMock])
            ->willReturnSelf();
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with(1)
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->repository->getList($cartId));
    }

    public function testDeleteById()
    {
        $cartId = 1;
        $itemId = 2;

        $cartItemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $childCartItemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->cartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->cartMock);
        $this->itemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->itemMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->itemMock, $itemId);
        $this->cartMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$cartItemMock]);
        $this->cartMock->expects($this->once())
            ->method('getInnerItems')
            ->willReturn([$cartItemMock, $childCartItemMock]);
        $cartItemMock->expects($this->exactly(2))
            ->method('getItemId')
            ->willReturn($itemId);
        $cartItemMock->expects($this->once())
            ->method('getParentItemId')
            ->willReturn(null);
        $childCartItemMock->expects($this->once())
            ->method('getParentItemId')
            ->willReturn($itemId);
        $childCartItemMock->expects($this->once())
            ->method('setIsDeleted')
            ->with(true);
        $this->itemComparatorMock->expects($this->once())
            ->method('isEquals')
            ->willReturn(true);
        $cartItemMock->expects($this->once())
            ->method('setIsDeleted')
            ->with(true);
        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->cartMock);

        $this->assertSame($this->cartMock, $this->repository->deleteById($cartId, $itemId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not remove item from cart.
     */
    public function testDeleteByIdException()
    {
        $cartId = 1;
        $itemId = 2;

        $cartItemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $childCartItemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->cartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->cartMock);
        $this->itemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->itemMock);
        $this->cartMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$cartItemMock]);
        $this->cartMock->expects($this->once())
            ->method('getInnerItems')
            ->willReturn([$cartItemMock, $childCartItemMock]);
        $cartItemMock->expects($this->exactly(2))
            ->method('getItemId')
            ->willReturn($itemId);
        $cartItemMock->expects($this->once())
            ->method('getParentItemId')
            ->willReturn(null);
        $childCartItemMock->expects($this->once())
            ->method('getParentItemId')
            ->willReturn($itemId);
        $childCartItemMock->expects($this->once())
            ->method('setIsDeleted')
            ->with(true);
        $this->itemComparatorMock->expects($this->once())
            ->method('isEquals')
            ->willReturn(true);
        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->cartMock)
            ->willThrowException(new \Exception());

        $this->repository->deleteById($cartId, $itemId);
    }
}
