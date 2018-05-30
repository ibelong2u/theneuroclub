<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart\Totals\Collectors\Subtotal;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ItemsRegistry;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Subtotal\InitialFee;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Subtotal\InitialFee
 */
class InitialFeeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InitialFee
     */
    private $collector;

    /**
     * @var ItemsRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressItemsRegistryMock;

    /**
     * @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartMock;

    /**
     * @var SubscriptionsCartAddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMock;

    /**
     * @var SubscriptionsCartTotalsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->addressItemsRegistryMock = $this->createMock(ItemsRegistry::class);
        $this->cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $this->addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $this->totalsMock = $this->getMockForAbstractClass(SubscriptionsCartTotalsInterface::class);
        $this->collector = $objectManager->getObject(
            InitialFee::class,
            ['addressItemsRegistry' => $this->addressItemsRegistryMock]
        );
    }

    public function testCollect()
    {
        $baseInitialFee1 = 2.00;
        $initialFee1 = 1.00;
        $qty1 = 1;

        $baseInitialFee2 = 3.00;
        $initialFee2 = 1.50;
        $qty2 = 2;

        $item1Mock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $item2Mock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->addressItemsRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($this->addressMock, $this->cartMock)
            ->willReturn([$item1Mock, $item2Mock]);
        $item1Mock->expects($this->once())
            ->method('getIsDeleted')
            ->willReturn(false);
        $item2Mock->expects($this->once())
            ->method('getIsDeleted')
            ->willReturn(false);
        $item1Mock->expects($this->once())
            ->method('getBaseInitialFee')
            ->willReturn($baseInitialFee1);
        $item2Mock->expects($this->once())
            ->method('getBaseInitialFee')
            ->willReturn($baseInitialFee2);
        $item1Mock->expects($this->once())
            ->method('getInitialFee')
            ->willReturn($initialFee1);
        $item2Mock->expects($this->once())
            ->method('getInitialFee')
            ->willReturn($initialFee2);
        $item1Mock->expects($this->exactly(2))
            ->method('getQty')
            ->willReturn($qty1);
        $item2Mock->expects($this->exactly(2))
            ->method('getQty')
            ->willReturn($qty2);
        $this->totalsMock->expects($this->once())
            ->method('setBaseInitialFee')
            ->with($baseInitialFee1 * $qty1 + $baseInitialFee2 * $qty2)
            ->willReturnSelf();
        $this->totalsMock->expects($this->once())
            ->method('setInitialFee')
            ->with($initialFee1 * $qty1 + $initialFee2 * $qty2)
            ->willReturnSelf();

        $this->collector->collect($this->cartMock, $this->addressMock, $this->totalsMock);
    }

    public function testCollectForDeletedItem()
    {
        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->addressItemsRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($this->addressMock, $this->cartMock)
            ->willReturn([$itemMock]);
        $itemMock->expects($this->once())
            ->method('getIsDeleted')
            ->willReturn(true);
        $this->totalsMock->expects($this->once())
            ->method('setBaseInitialFee')
            ->with(0)
            ->willReturnSelf();
        $this->totalsMock->expects($this->once())
            ->method('setInitialFee')
            ->with(0)
            ->willReturnSelf();

        $this->collector->collect($this->cartMock, $this->addressMock, $this->totalsMock);
    }
}
