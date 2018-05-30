<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\ResourceModel;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart as SubscriptionsCartResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart
 */
class SubscriptionsCartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubscriptionsCartResource
     */
    private $cartResource;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourcesMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->resourcesMock = $this->createMock(ResourceConnection::class);
        $context = $objectManager->getObject(Context::class, ['resources' => $this->resourcesMock]);

        $this->cartResource = $objectManager->getObject(
            SubscriptionsCartResource::class,
            [
                'context' => $context,
                'metadataPool' => $this->metadataPoolMock
            ]
        );
    }

    /**
     * Set up mocks for getConnection() method
     *
     * @param AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $connectionMock
     */
    private function setUpGetConnection($connectionMock)
    {
        $connectionName = 'connection';

        $metadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);

        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with(SubscriptionsCartInterface::class)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getEntityConnectionName')
            ->willReturn($connectionName);
        $this->resourcesMock->expects($this->once())
            ->method('getConnectionByName')
            ->with($connectionName)
            ->willReturn($connectionMock);
    }

    public function testGetConnection()
    {
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->setUpGetConnection($connectionMock);

        $this->assertEquals($connectionMock, $this->cartResource->getConnection());
    }

    public function testGetCartIdByCustomerId()
    {
        $customerId = 1;
        $cartId = 2;
        $tableName = 'aw_sarp_subscriptions_cart';

        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);

        $this->setUpGetConnection($connectionMock);
        $connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $this->resourcesMock->expects($this->once())
            ->method('getTableName')
            ->with($tableName)
            ->willReturn($tableName);
        $selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, 'cart_id')
            ->willReturnSelf();
        $selectMock->expects($this->exactly(2))
            ->method('where')
            ->withConsecutive(['customer_id = :customerId'], ['is_active = ?', 1])
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('order')
            ->with('updated_at DESC')
            ->willReturnSelf();
        $connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($selectMock, ['customerId' => $customerId])
            ->willReturn($cartId);

        $this->assertEquals($cartId, $this->cartResource->getCartIdByCustomerId($customerId));
    }
}
