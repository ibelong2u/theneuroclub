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
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Subtotal\Trial;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Subtotal\Trial
 */
class TrialTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Trial
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
            Trial::class,
            ['addressItemsRegistry' => $this->addressItemsRegistryMock]
        );
    }

    public function testCollect()
    {
        $baseTrialPrice1 = 7.00;
        $trialPrice1 = 3.50;
        $qty1 = 1;
        $baseTrialRowTotal1 = $baseTrialPrice1 * $qty1;
        $trialRowTotal1 = $trialPrice1 * $qty1;

        $baseTrialPrice2 = 20.00;
        $trialPrice2 = 10.00;
        $qty2 = 2;
        $baseTrialRowTotal2 = $baseTrialPrice2 * $qty2;
        $trialRowTotal2 = $trialPrice2 * $qty2;

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
            ->method('getBaseTrialPrice')
            ->willReturn($baseTrialPrice1);
        $item2Mock->expects($this->once())
            ->method('getBaseTrialPrice')
            ->willReturn($baseTrialPrice2);
        $item1Mock->expects($this->once())
            ->method('getTrialPrice')
            ->willReturn($trialPrice1);
        $item2Mock->expects($this->once())
            ->method('getTrialPrice')
            ->willReturn($trialPrice2);
        $item1Mock->expects($this->exactly(2))
            ->method('getQty')
            ->willReturn($qty1);
        $item2Mock->expects($this->exactly(2))
            ->method('getQty')
            ->willReturn($qty2);
        $item1Mock->expects($this->once())
            ->method('setBaseTrialRowTotal')
            ->with($baseTrialRowTotal1)
            ->willReturnSelf();
        $item2Mock->expects($this->once())
            ->method('setBaseTrialRowTotal')
            ->with($baseTrialRowTotal2)
            ->willReturnSelf();
        $item1Mock->expects($this->once())
            ->method('setTrialRowTotal')
            ->with($trialRowTotal1)
            ->willReturnSelf();
        $item2Mock->expects($this->once())
            ->method('setTrialRowTotal')
            ->with($trialRowTotal2)
            ->willReturnSelf();
        $item1Mock->expects($this->once())
            ->method('getBaseTrialRowTotal')
            ->willReturn($baseTrialRowTotal1);
        $item2Mock->expects($this->once())
            ->method('getBaseTrialRowTotal')
            ->willReturn($baseTrialRowTotal2);
        $item1Mock->expects($this->once())
            ->method('getTrialRowTotal')
            ->willReturn($trialRowTotal1);
        $item2Mock->expects($this->once())
            ->method('getTrialRowTotal')
            ->willReturn($trialRowTotal2);
        $this->totalsMock->expects($this->once())
            ->method('setBaseTrialSubtotal')
            ->with($baseTrialRowTotal1 + $baseTrialRowTotal2)
            ->willReturnSelf();
        $this->totalsMock->expects($this->once())
            ->method('setTrialSubtotal')
            ->with($trialRowTotal1 + $trialRowTotal2)
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
            ->method('setBaseTrialSubtotal')
            ->with(0)
            ->willReturnSelf();
        $this->totalsMock->expects($this->once())
            ->method('setTrialSubtotal')
            ->with(0)
            ->willReturnSelf();

        $this->collector->collect($this->cartMock, $this->addressMock, $this->totalsMock);
    }
}
