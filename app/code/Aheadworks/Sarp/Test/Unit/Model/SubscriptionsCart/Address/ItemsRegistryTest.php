<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart\Address;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ItemsRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Address\ItemsRegistry
 */
class ItemsRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ItemsRegistry
     */
    private $itemsRegistry;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->itemsRegistry = $objectManager->getObject(ItemsRegistry::class);
    }

    /**
     * @param SubscriptionsCartAddressInterface|\PHPUnit_Framework_MockObject_MockObject $addressMock
     * @param SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject $cartMock
     * @param array $result
     * @dataProvider retrieveDataProvider
     */
    public function testRetrieve($addressMock, $cartMock, $result)
    {
        $addressId = 1;

        $addressMock->expects($this->once())
            ->method('getAddressId')
            ->willReturn($addressId);

        $this->assertEquals($result, $this->itemsRegistry->retrieve($addressMock, $cartMock));
    }

    /**
     * Create cart mock
     *
     * @param bool $isVirtual
     * @param SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock
     * @return SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createCartMock($isVirtual, $itemMock)
    {
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);

        $cartMock->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn($isVirtual);
        $cartMock->expects($this->any())
            ->method('getItems')
            ->willReturn([$itemMock]);

        return $cartMock;
    }

    /**
     * Create address mock
     *
     * @param string $addressType
     * @return SubscriptionsCartAddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createAddressMock($addressType)
    {
        $addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);

        $addressMock->expects($this->once())
            ->method('getAddressType')
            ->willReturn($addressType);

        return $addressMock;
    }

    /**
     * @return array
     */
    public function retrieveDataProvider()
    {
        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        return [
            'billing address, virtual cart' => [
                $this->createAddressMock(Address::TYPE_BILLING),
                $this->createCartMock(true, $itemMock),
                [$itemMock]
            ],
            'billing address, non virtual cart' => [
                $this->createAddressMock(Address::TYPE_BILLING),
                $this->createCartMock(false, $itemMock),
                []
            ],
            'shipping address, virtual cart' => [
                $this->createAddressMock(Address::TYPE_SHIPPING),
                $this->createCartMock(true, $itemMock),
                []
            ],
            'shipping address, non virtual cart' => [
                $this->createAddressMock(Address::TYPE_SHIPPING),
                $this->createCartMock(false, $itemMock),
                [$itemMock]
            ]
        ];
    }
}
