<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\CustomerData;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\CustomerData\SubscriptionCart;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\CustomerData\SubscriptionCart
 */
class SubscriptionCartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubscriptionCart
     */
    private $sectionSource;

    /**
     * @var Persistor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartPersistorMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->cartPersistorMock = $this->createMock(Persistor::class);
        $this->sectionSource = $objectManager->getObject(
            SubscriptionCart::class,
            ['cartPersistor' => $this->cartPersistorMock]
        );
    }

    public function testGetSectionData()
    {
        $itemsCount = 9;

        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $this->cartPersistorMock->expects($this->once())
            ->method('getSubscriptionCart')
            ->willReturn($cartMock);

        $cartItemOneMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $cartItemOneMock->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(1);
        $cartItemTwoMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $cartItemTwoMock->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(3);
        $cartItemThreeMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $cartItemThreeMock->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(5);

        $cartMock->expects($this->once())
            ->method('getItems')
            ->willReturn(
                [
                    $cartItemOneMock,
                    $cartItemTwoMock,
                    $cartItemThreeMock
                ]
            );

        $this->assertEquals(['itemsCount' => $itemsCount], $this->sectionSource->getSectionData());
    }
}
