<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart\Totals\Collectors;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ItemsRegistry;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Subtotal;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Subtotal
 */
class SubtotalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Subtotal
     */
    private $collector;

    /**
     * @var ItemsRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressItemsRegistryMock;

    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

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
        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $this->addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $this->totalsMock = $this->getMockForAbstractClass(SubscriptionsCartTotalsInterface::class);
        $this->collector = $objectManager->getObject(
            Subtotal::class,
            [
                'addressItemsRegistry' => $this->addressItemsRegistryMock,
                'priceCurrency' => $this->priceCurrencyMock
            ]
        );
    }

    public function testCollect()
    {
        $baseRegularPrice1 = 10.00;
        $regularPrice1 = 5.00;
        $qty1 = 2;
        $baseRowTotal1 = $baseRegularPrice1 * $qty1;
        $rowTotal1 = $regularPrice1 * $qty1;

        $baseRegularPrice2 = 15.00;
        $regularPrice2 = 7.50;
        $qty2 = 1;
        $baseRowTotal2 = $baseRegularPrice2 * $qty2;
        $rowTotal2 = $regularPrice2 * $qty2;

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
            ->method('getBaseRegularPrice')
            ->willReturn($baseRegularPrice1);
        $item2Mock->expects($this->once())
            ->method('getBaseRegularPrice')
            ->willReturn($baseRegularPrice2);
        $this->priceCurrencyMock->expects($this->exactly(2))
            ->method('convert')
            ->withConsecutive([$baseRegularPrice1], [$baseRegularPrice2])
            ->willReturnOnConsecutiveCalls($regularPrice1, $regularPrice2);
        $item1Mock->expects($this->exactly(2))
            ->method('getQty')
            ->willReturn($qty1);
        $item2Mock->expects($this->exactly(2))
            ->method('getQty')
            ->willReturn($qty2);
        $item1Mock->expects($this->once())
            ->method('setBaseRowTotal')
            ->with($baseRowTotal1)
            ->willReturnSelf();
        $item2Mock->expects($this->once())
            ->method('setBaseRowTotal')
            ->with($baseRowTotal2)
            ->willReturnSelf();
        $item1Mock->expects($this->once())
            ->method('setRowTotal')
            ->with($rowTotal1)
            ->willReturnSelf();
        $item2Mock->expects($this->once())
            ->method('setRowTotal')
            ->with($rowTotal2)
            ->willReturnSelf();
        $item1Mock->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($baseRowTotal1);
        $item2Mock->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($baseRowTotal2);
        $item1Mock->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($rowTotal1);
        $item2Mock->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($rowTotal2);
        $this->totalsMock->expects($this->once())
            ->method('setBaseSubtotal')
            ->with($baseRowTotal1 + $baseRowTotal2)
            ->willReturnSelf();
        $this->totalsMock->expects($this->once())
            ->method('setSubtotal')
            ->with($rowTotal1 + $rowTotal2)
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
        $this->priceCurrencyMock->expects($this->never())
            ->method('convert');
        $itemMock->expects($this->never())
            ->method('setBaseRowTotal');
        $itemMock->expects($this->never())
            ->method('setRowTotal');
        $this->totalsMock->expects($this->once())
            ->method('setBaseSubtotal')
            ->with(0)
            ->willReturnSelf();
        $this->totalsMock->expects($this->once())
            ->method('setSubtotal')
            ->with(0)
            ->willReturnSelf();

        $this->collector->collect($this->cartMock, $this->addressMock, $this->totalsMock);
    }
}
