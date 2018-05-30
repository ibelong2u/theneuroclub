<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart\Address;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\SaveHandler;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Address\SaveHandler
 */
class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->saveHandler = $objectManager->getObject(
            SaveHandler::class,
            ['entityManager' => $this->entityManagerMock]
        );
    }

    public function testExecute()
    {
        $cartId = 1;

        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);

        $cartMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$addressMock]);
        $cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $addressMock->expects($this->once())
            ->method('setCartId')
            ->with($cartId);
        $addressMock->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn(null);
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($addressMock);

        $this->assertEquals($cartMock, $this->saveHandler->execute($cartMock));
    }

    public function testExecuteSetCustomerEmail()
    {
        $cartId = 1;
        $customerAddressId = 2;
        $customerId = 3;
        $customerEmail = 'master@master.com';

        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);

        $cartMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$addressMock]);
        $cartMock->expects($this->once())
            ->method('getCartId')
            ->willReturn($cartId);
        $addressMock->expects($this->once())
            ->method('setCartId')
            ->with($cartId);
        $addressMock->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn($customerAddressId);
        $cartMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $addressMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $cartMock->expects($this->once())
            ->method('getCustomerEmail')
            ->willReturn($customerEmail);
        $addressMock->expects($this->once())
            ->method('setEmail')
            ->with($customerEmail);
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($addressMock);

        $this->assertEquals($cartMock, $this->saveHandler->execute($cartMock));
    }
}
