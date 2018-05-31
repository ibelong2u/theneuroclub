<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart\Item;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterfaceFactory;
use Aheadworks\Sarp\Model\SubscriptionsCart\Item\ReadHandler;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Item\ReadHandler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReadHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var SubscriptionsCartItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemsFactoryMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->itemsFactoryMock = $this->createMock(SubscriptionsCartItemInterfaceFactory::class);
        $this->readHandler = $objectManager->getObject(
            ReadHandler::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
                'metadataPool' => $this->metadataPoolMock,
                'entityManager' => $this->entityManagerMock,
                'itemsFactory' => $this->itemsFactoryMock
            ]
        );
    }

    public function testExecute()
    {
        $cartId = 1;
        $itemId = 2;
        $connectionName = 'default';
        $tableName = 'aw_sarp_subscriptions_cart_item';

        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $metadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);

        $cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with(SubscriptionsCartItemInterface::class)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getEntityConnectionName')
            ->willReturn($connectionName);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnectionByName')
            ->with($connectionName)
            ->willReturn($connectionMock);
        $connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->willReturnArgument(0);
        $selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, 'item_id')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with('cart_id = :id')
            ->willReturnSelf();
        $connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($selectMock, ['id' => $cartId])
            ->willReturn([$itemId]);
        $this->itemsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($itemMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($itemMock, $itemId);
        $itemMock->expects($this->once())
            ->method('getParentItemId')
            ->willReturn(null);
        $cartMock->expects($this->once())
            ->method('setInnerItems')
            ->with([$itemMock])
            ->willReturnSelf();
        $cartMock->expects($this->once())
            ->method('setItems')
            ->with([$itemMock]);

        $this->assertEquals($cartMock, $this->readHandler->execute($cartMock));
    }
}
