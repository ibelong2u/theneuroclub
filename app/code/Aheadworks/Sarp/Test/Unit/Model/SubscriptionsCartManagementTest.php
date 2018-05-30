<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model;

use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterfaceFactory;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionsCartManagement;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Aheadworks\Sarp\Model\SubscriptionsCart\ItemsAddToCartValidator;
use Aheadworks\Sarp\Model\SubscriptionsCart\ItemsComparator;
use Aheadworks\Sarp\Model\SubscriptionsCart\ItemsProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCartManagement
 */
class SubscriptionsCartManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubscriptionsCartManagement
     */
    private $cartManagement;

    /**
     * @var SubscriptionsCartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var SubscriptionsCartAddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressFactoryMock;

    /**
     * @var ItemsComparator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemsComparatorMock;

    /**
     * @var ItemsProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemsProcessorMock;

    /**
     * @var ItemsAddToCartValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemsValidatorMock;

    /**
     * @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->cartRepositoryMock = $this->getMockForAbstractClass(SubscriptionsCartRepositoryInterface::class);
        $this->addressFactoryMock = $this->createMock(SubscriptionsCartAddressInterfaceFactory::class);
        $this->itemsComparatorMock = $this->createMock(ItemsComparator::class);
        $this->itemsProcessorMock = $this->createMock(ItemsProcessor::class);
        $this->itemsValidatorMock = $this->createMock(ItemsAddToCartValidator::class);

        $this->cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);

        $this->cartManagement = $objectManager->getObject(
            SubscriptionsCartManagement::class,
            [
                'subscriptionsCartRepository' => $this->cartRepositoryMock,
                'addressFactory' => $this->addressFactoryMock,
                'itemsComparator' => $this->itemsComparatorMock,
                'itemsProcessor' => $this->itemsProcessorMock,
                'itemsValidator' => $this->itemsValidatorMock
            ]
        );
    }

    /**
     * Set up mocks for initAddresses() method
     */
    private function setUpInitAddresses()
    {
        $billingAddressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $shippingAddressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);

        $this->addressFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                $billingAddressMock,
                $shippingAddressMock
            );
        $billingAddressMock->expects($this->once())
            ->method('setAddressType')
            ->with(Address::TYPE_BILLING)
            ->willReturnSelf();
        $shippingAddressMock->expects($this->once())
            ->method('setAddressType')
            ->with(Address::TYPE_SHIPPING)
            ->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('setAddresses')
            ->with([$billingAddressMock, $shippingAddressMock]);
    }

    /**
     * Set up mocks for findSameItem() method
     *
     * @param SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock
     * @param SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject $listItemMock
     * @param bool $result
     */
    private function setUpFindSameItem($itemMock, $listItemMock, $result)
    {
        $this->itemsComparatorMock->expects($this->once())
            ->method('isEquals')
            ->with($itemMock, $listItemMock)
            ->willReturn($result);
    }

    public function testAddNew()
    {
        /** @var SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $cartItemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->itemsValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($itemMock)
            ->willReturn(true);
        $this->itemsProcessorMock->expects($this->once())
            ->method('processBeforeAdd')
            ->with($this->cartMock, $itemMock)
            ->willReturn([$itemMock]);
        $this->cartMock->expects($this->once())
            ->method('getInnerItems')
            ->willReturn([$cartItemMock]);
        $this->setUpFindSameItem($itemMock, $cartItemMock, false);
        $this->cartMock->expects($this->once())
            ->method('setInnerItems')
            ->with([$itemMock, $cartItemMock])
            ->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('setSubscriptionPlanId')
            ->with(null)
            ->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn(null);
        $this->setUpInitAddresses();
        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->cartMock);

        $this->assertEquals(
            $itemMock,
            $this->cartManagement->add($this->cartMock, $itemMock)
        );
    }

    public function testAddExisting()
    {
        $itemQty = 1;
        $cartItemQty = 2;

        /** @var SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $cartItemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->itemsValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($itemMock)
            ->willReturn(true);
        $this->itemsProcessorMock->expects($this->once())
            ->method('processBeforeAdd')
            ->with($this->cartMock, $itemMock)
            ->willReturn([$itemMock]);
        $this->cartMock->expects($this->once())
            ->method('getInnerItems')
            ->willReturn([$cartItemMock]);
        $this->setUpFindSameItem($itemMock, $cartItemMock, true);
        $itemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($itemQty);
        $cartItemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($cartItemQty);
        $cartItemMock->expects($this->once())
            ->method('setQty')
            ->with($cartItemQty + $itemQty);
        $this->cartMock->expects($this->once())
            ->method('setInnerItems')
            ->with([$cartItemMock])
            ->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('setSubscriptionPlanId')
            ->with(null)
            ->willReturnSelf();
        $this->cartMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn(
                [
                    $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class),
                    $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class)
                ]
            );
        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->cartMock);

        $this->assertEquals(
            $itemMock,
            $this->cartManagement->add($this->cartMock, $itemMock)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Item cannot be added to subscription cart.
     */
    public function testAddException()
    {
        /** @var SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->itemsValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($itemMock)
            ->willReturn(false);

        $this->cartManagement->add($this->cartMock, $itemMock);
    }

    public function testSelectSubscriptionPlan()
    {
        $cartId = 1;
        $planId = 2;

        $this->cartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->cartMock);
        $this->cartMock->expects($this->once())
            ->method('setSubscriptionPlanId')
            ->with($planId);
        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->cartMock)
            ->willReturn($this->cartMock);

        $this->assertEquals(
            $this->cartMock,
            $this->cartManagement->selectSubscriptionPlan($cartId, $planId)
        );
    }
}
