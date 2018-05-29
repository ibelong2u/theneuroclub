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
use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Api\SubscriptionPlanRepositoryInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ItemsRegistry;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\InitialFee;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\SubscriptionPriceCalculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\InitialFee
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var SubscriptionPlanRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planRepositoryMock;

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
        $this->planRepositoryMock = $this->getMockForAbstractClass(SubscriptionPlanRepositoryInterface::class);
        $this->priceCalculatorMock = $this->createMock(SubscriptionPriceCalculator::class);
        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);

        $this->cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $this->addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $this->totalsMock = $this->getMockForAbstractClass(SubscriptionsCartTotalsInterface::class);

        $this->collector = $objectManager->getObject(
            InitialFee::class,
            [
                'addressItemsRegistry' => $this->addressItemsRegistryMock,
                'planRepository' => $this->planRepositoryMock,
                'priceCalculator' => $this->priceCalculatorMock,
                'priceCurrency' => $this->priceCurrencyMock
            ]
        );
    }

    public function testCollect()
    {
        $itemId = 1;
        $planId = 2;
        $baseInitialFee = 10.00;
        $initialFee = 5.00;

        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $childItemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $planMock = $this->getMockForAbstractClass(SubscriptionPlanInterface::class);

        $this->addressItemsRegistryMock->expects($this->once())
            ->method('retrieveInner')
            ->with($this->addressMock, $this->cartMock)
            ->willReturn([$itemMock]);
        $itemMock->expects($this->once())
            ->method('getIsDeleted')
            ->willReturn(false);
        $this->cartMock->expects($this->exactly(2))
            ->method('getSubscriptionPlanId')
            ->willReturn($planId);
        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willReturn($planMock);
        $planMock->expects($this->once())
            ->method('getIsInitialFeeEnabled')
            ->willReturn(true);
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
            ->method('getBaseInitialFee')
            ->with($itemMock, $childItemMock)
            ->willReturn($baseInitialFee);
        $this->priceCurrencyMock->expects($this->once())
            ->method('convert')
            ->with($baseInitialFee)
            ->willReturn($initialFee);
        $itemMock->expects($this->once())
            ->method('setBaseInitialFee')
            ->with($baseInitialFee)
            ->willReturnSelf();
        $itemMock->expects($this->once())
            ->method('setInitialFee')
            ->with($initialFee)
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
        $this->planRepositoryMock->expects($this->never())
            ->method('get');
        $this->addressItemsRegistryMock->expects($this->never())
            ->method('retrieveChild');
        $this->priceCalculatorMock->expects($this->never())
            ->method('getBaseInitialFee');
        $this->priceCurrencyMock->expects($this->never())
            ->method('convert');
        $itemMock->expects($this->never())
            ->method('setBaseInitialFee');
        $itemMock->expects($this->never())
            ->method('setInitialFee');

        $this->collector->collect($this->cartMock, $this->addressMock, $this->totalsMock);
    }

    public function testCollectWhenPlanIsNotSelected()
    {
        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->addressItemsRegistryMock->expects($this->once())
            ->method('retrieveInner')
            ->with($this->addressMock, $this->cartMock)
            ->willReturn([$itemMock]);
        $itemMock->expects($this->once())
            ->method('getIsDeleted')
            ->willReturn(false);
        $this->cartMock->expects($this->once())
            ->method('getSubscriptionPlanId')
            ->willReturn(null);
        $this->planRepositoryMock->expects($this->never())
            ->method('get');
        $this->priceCalculatorMock->expects($this->never())
            ->method('getBaseInitialFee');
        $this->priceCurrencyMock->expects($this->once())
            ->method('convert')
            ->with(0)
            ->willReturn(0);
        $itemMock->expects($this->once())
            ->method('setBaseInitialFee')
            ->with(0)
            ->willReturnSelf();
        $itemMock->expects($this->once())
            ->method('setInitialFee')
            ->with(0)
            ->willReturnSelf();

        $this->collector->collect($this->cartMock, $this->addressMock, $this->totalsMock);
    }

    public function testCollectWhenInitialFeeIsNotEnabled()
    {
        $planId = 1;

        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $planMock = $this->getMockForAbstractClass(SubscriptionPlanInterface::class);

        $this->addressItemsRegistryMock->expects($this->once())
            ->method('retrieveInner')
            ->with($this->addressMock, $this->cartMock)
            ->willReturn([$itemMock]);
        $itemMock->expects($this->once())
            ->method('getIsDeleted')
            ->willReturn(false);
        $this->cartMock->expects($this->exactly(2))
            ->method('getSubscriptionPlanId')
            ->willReturn($planId);
        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willReturn($planMock);
        $planMock->expects($this->once())
            ->method('getIsInitialFeeEnabled')
            ->willReturn(false);
        $this->priceCalculatorMock->expects($this->never())
            ->method('getBaseInitialFee');
        $this->priceCurrencyMock->expects($this->once())
            ->method('convert')
            ->with(0)
            ->willReturn(0);
        $itemMock->expects($this->once())
            ->method('setBaseInitialFee')
            ->with(0)
            ->willReturnSelf();
        $itemMock->expects($this->once())
            ->method('setInitialFee')
            ->with(0)
            ->willReturnSelf();

        $this->collector->collect($this->cartMock, $this->addressMock, $this->totalsMock);
    }
}
