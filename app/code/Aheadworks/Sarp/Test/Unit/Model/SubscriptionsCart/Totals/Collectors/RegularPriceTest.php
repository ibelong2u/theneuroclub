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
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\RegularPrice;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\SubscriptionPriceCalculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\RegularPrice
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RegularPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RegularPrice
     */
    private $collector;

    /**
     * @var ItemsRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressItemsRegistryMock;

    /**
     * @var SubscriptionPriceCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCalculatorMock;

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
        $this->priceCalculatorMock = $this->createMock(SubscriptionPriceCalculator::class);
        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);

        $this->cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $this->addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $this->totalsMock = $this->getMockForAbstractClass(SubscriptionsCartTotalsInterface::class);

        $this->collector = $objectManager->getObject(
            RegularPrice::class,
            [
                'addressItemsRegistry' => $this->addressItemsRegistryMock,
                'priceCalculator' => $this->priceCalculatorMock,
                'priceCurrency' => $this->priceCurrencyMock
            ]
        );
    }

    public function testCollect()
    {
        $itemId = 1;
        $baseRegularPrice = 10.00;
        $regularPrice = 5.00;

        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $childItemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->addressItemsRegistryMock->expects($this->once())
            ->method('retrieveInner')
            ->with($this->addressMock, $this->cartMock)
            ->willReturn([$itemMock]);
        $itemMock->expects($this->once())
            ->method('getIsDeleted')
            ->willReturn(false);
        $itemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn($itemId);
        $this->addressItemsRegistryMock->expects($this->once())
            ->method('retrieveChild')
            ->with(
                $this->addressMock,
                $this->cartMock,
                $itemId
            )
            ->willReturn($childItemMock);
        $this->priceCalculatorMock->expects($this->once())
            ->method('getBaseRegularPrice')
            ->with($itemMock, $childItemMock)
            ->willReturn($baseRegularPrice);
        $this->priceCurrencyMock->expects($this->once())
            ->method('convert')
            ->with($baseRegularPrice)
            ->willReturn($regularPrice);
        $itemMock->expects($this->once())
            ->method('setBaseRegularPrice')
            ->with($baseRegularPrice)
            ->willReturnSelf();
        $itemMock->expects($this->once())
            ->method('setRegularPrice')
            ->with($regularPrice)
            ->willReturnSelf();

        $this->collector->collect($this->cartMock, $this->addressMock, $this->totalsMock);
    }

    public function testCollectForDeletedItem()
    {
        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->addressItemsRegistryMock->expects($this->once())
            ->method('retrieveInner')
            ->with($this->addressMock, $this->cartMock)
            ->willReturn([$itemMock]);
        $itemMock->expects($this->once())
            ->method('getIsDeleted')
            ->willReturn(true);
        $this->addressItemsRegistryMock->expects($this->never())
            ->method('retrieveChild');
        $this->priceCalculatorMock->expects($this->never())
            ->method('getBaseRegularPrice');
        $this->priceCurrencyMock->expects($this->never())
            ->method('convert');
        $itemMock->expects($this->never())
            ->method('setBaseRegularPrice');
        $itemMock->expects($this->never())
            ->method('setRegularPrice');

        $this->collector->collect($this->cartMock, $this->addressMock, $this->totalsMock);
    }
}
