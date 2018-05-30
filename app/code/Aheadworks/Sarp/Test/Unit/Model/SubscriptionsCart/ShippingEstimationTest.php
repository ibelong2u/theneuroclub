<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Model\Quote\ShippingMethod\Converter as ShippingMethodConverter;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ConverterManager as AddressConverterManager;
use Aheadworks\Sarp\Model\SubscriptionsCart\TotalsCollector;
use Aheadworks\Sarp\Model\SubscriptionsCart\Shipping\RatesCollector;
use Aheadworks\Sarp\Model\SubscriptionsCart\ShippingEstimation;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Quote\Address\RateResult\Method as RateResultMethod;
use Magento\Shipping\Model\Rate\Result as RateResult;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\ShippingEstimation
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingEstimationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShippingEstimation
     */
    private $shippingEstimation;

    /**
     * @var RatesCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ratesCollectorMock;

    /**
     * @var SubscriptionsCartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAddressRepositoryMock;

    /**
     * @var TotalsCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsCollectorMock;

    /**
     * @var AddressConverterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressConverterManagerMock;

    /**
     * @var ShippingMethodConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingMethodConverterMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->ratesCollectorMock = $this->createMock(RatesCollector::class);
        $this->cartRepositoryMock = $this->getMockForAbstractClass(SubscriptionsCartRepositoryInterface::class);
        $this->customerAddressRepositoryMock = $this->getMockForAbstractClass(AddressRepositoryInterface::class);
        $this->totalsCollectorMock = $this->createMock(TotalsCollector::class);
        $this->addressConverterManagerMock = $this->createMock(AddressConverterManager::class);
        $this->shippingMethodConverterMock = $this->createMock(ShippingMethodConverter::class);
        $this->shippingEstimation = $objectManager->getObject(
            ShippingEstimation::class,
            [
                'ratesCollector' => $this->ratesCollectorMock,
                'cartRepository' => $this->cartRepositoryMock,
                'customerAddressRepository' => $this->customerAddressRepositoryMock,
                'totalsCollector' => $this->totalsCollectorMock,
                'addressConverterManager' => $this->addressConverterManagerMock,
                'shippingMethodConverter' => $this->shippingMethodConverterMock
            ]
        );
    }

    /**
     * Set up mocks for estimate() method
     *
     * @param int $cartId
     * @param SubscriptionsCartAddressInterface|\PHPUnit_Framework_MockObject_MockObject $shippingAddressMock
     * @param ShippingMethodInterface|\PHPUnit_Framework_MockObject_MockObject $shippingMethodMock
     * @return void
     */
    private function setUpEstimate($cartId, $shippingAddressMock, $shippingMethodMock)
    {
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $rateResultMock = $this->createMock(RateResult::class);
        $rateMethodMock = $this->createMock(RateResultMethod::class);

        $this->cartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($cartMock);
        $this->totalsCollectorMock->expects($this->once())
            ->method('collectAddressTotals')
            ->with($cartMock, $shippingAddressMock);
        $this->ratesCollectorMock->expects($this->once())
            ->method('collect')
            ->with($shippingAddressMock, $cartMock)
            ->willReturn($rateResultMock);
        $rateResultMock->expects($this->once())
            ->method('getAllRates')
            ->willReturn([$rateMethodMock]);
        $this->shippingMethodConverterMock->expects($this->once())
            ->method('fromRateResultMethod')
            ->with($rateMethodMock)
            ->willReturn($shippingMethodMock);
    }

    public function testEstimate()
    {
        $cartId = 1;
        /** @var SubscriptionsCartAddressInterface|\PHPUnit_Framework_MockObject_MockObject $shippingAddressMock */
        $shippingAddressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $shippingMethodMock = $this->getMockForAbstractClass(ShippingMethodInterface::class);
        $this->setUpEstimate($cartId, $shippingAddressMock, $shippingMethodMock);
        $this->assertEquals(
            [$shippingMethodMock],
            $this->shippingEstimation->estimate($cartId, $shippingAddressMock)
        );
    }

    public function testEstimateByCustomerAddressId()
    {
        $cartId = 1;
        $customerAddressId = 2;

        /** @var SubscriptionsCartAddressInterface|\PHPUnit_Framework_MockObject_MockObject $addressMock */
        $addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $shippingMethodMock = $this->getMockForAbstractClass(ShippingMethodInterface::class);
        $customerAddressMock = $this->getMockForAbstractClass(CustomerAddress::class);

        $this->customerAddressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerAddressId)
            ->willReturn($customerAddressMock);
        $this->addressConverterManagerMock->expects($this->once())
            ->method('fromCustomerAddress')
            ->with($customerAddressMock)
            ->willReturn($addressMock);
        $addressMock->expects($this->once())
            ->method('setAddressType')
            ->with(Address::TYPE_SHIPPING)
            ->willReturnSelf();
        $addressMock->expects($this->once())
            ->method('setCartId')
            ->with($cartId)
            ->willReturnSelf();
        $this->setUpEstimate($cartId, $addressMock, $shippingMethodMock);

        $this->assertEquals(
            [$shippingMethodMock],
            $this->shippingEstimation->estimateByCustomerAddressId($cartId, $customerAddressId)
        );
    }
}
