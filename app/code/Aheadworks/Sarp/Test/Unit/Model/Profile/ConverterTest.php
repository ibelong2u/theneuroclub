<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\Profile;

use Aheadworks\Sarp\Api\Data\PaymentMethodInterface;
use Aheadworks\Sarp\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Model\Profile\Converter;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfileInterfaceFactory;
use Aheadworks\Sarp\Model\Profile\Address\Converter as AddressConverter;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Resolver\FullName as FullNameResolver;
use Aheadworks\Sarp\Model\Profile\Item\Converter as ItemConverter;
use Aheadworks\Sarp\Model\Profile\StartDateResolver;
use Aheadworks\Sarp\Api\SubscriptionPlanRepositoryInterface;
use Aheadworks\Sarp\Model\PaymentMethodList;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\StartDateType;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DataObject\Copy;

/**
 * Test for \Aheadworks\Sarp\Model\Profile\Converter
 */
class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Converter
     */
    private $model;

    /**
     * @var ProfileInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileFactoryMock;

    /**
     * @var AddressConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressConverterMock;

    /**
     * @var FullNameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fullNameResolverMock;

    /**
     * @var ItemConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemConverterMock;

    /**
     * @var StartDateResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $startDateResolverMock;

    /**
     * @var SubscriptionPlanRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planRepositoryMock;

    /**
     * @var PaymentMethodList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethodListMock;

    /**
     * @var Copy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectCopyServiceMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->profileFactoryMock = $this->getMockBuilder(ProfileInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressConverterMock = $this->getMockBuilder(AddressConverter::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->fullNameResolverMock = $this->getMockBuilder(FullNameResolver::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemConverterMock = $this->getMockBuilder(ItemConverter::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->startDateResolverMock = $this->getMockBuilder(StartDateResolver::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->planRepositoryMock = $this->getMockBuilder(SubscriptionPlanRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->paymentMethodListMock = $this->getMockBuilder(PaymentMethodList::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectCopyServiceMock = $this->getMockBuilder(Copy::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            Converter::class,
            [
                'profileFactory' => $this->profileFactoryMock,
                'addressConverter' => $this->addressConverterMock,
                'fullNameResolver' => $this->fullNameResolverMock,
                'itemConverter' => $this->itemConverterMock,
                'startDateResolver' => $this->startDateResolverMock,
                'planRepository' => $this->planRepositoryMock,
                'paymentMethodList' => $this->paymentMethodListMock,
                'objectCopyService' => $this->objectCopyServiceMock
            ]
        );
    }

    /**
     * @param bool $isCartVirtual
     * @param bool $isPlanStartDate
     * @dataProvider fromSubscriptionCartDataProvider
     */
    public function testFromSubscriptionCart($isCartVirtual, $isPlanStartDate)
    {
        $subscriptionPlanId = 1;
        $startDate = '2020-01-01 00:00:00';
        $startDateType = StartDateType::EXACT_DAY_OF_MONTH;
        $dayOfMonth = 1;
        $engineCode = 'paypal';
        $paymentMethodCode = 'paypal_express';
        $paymentMethodTitle = 'PayPal Express';
        $customerFullName = 'Customer Name';

        $profileMock = $this->getMockBuilder(ProfileInterface::class)
            ->getMockForAbstractClass();
        $this->profileFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($profileMock);

        $cartItemMock = $this->getMockBuilder(SubscriptionsCartItemInterface::class)
            ->getMockForAbstractClass();
        $cartMock = $this->getMockBuilder(SubscriptionsCartInterface::class)
            ->getMockForAbstractClass();
        $cartMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$cartItemMock]);

        $profileItemMock = $this->getMockBuilder(ProfileItemInterface::class)
            ->getMockForAbstractClass();
        $this->itemConverterMock->expects($this->once())
            ->method('fromCartItem')
            ->with($cartItemMock, $cartMock)
            ->willReturn($profileItemMock);

        $profileMock->expects($this->once())
            ->method('setItems')
            ->with([$profileItemMock])
            ->willReturnSelf();
        $profileMock->expects($this->once())
            ->method('setInnerItems')
            ->with([$profileItemMock])
            ->willReturnSelf();

        if ($isCartVirtual) {
            $cartMock->expects($this->once())
                ->method('getIsVirtual')
                ->willReturn($isCartVirtual);
            $cartAddressMock = $this->getMockBuilder(SubscriptionsCartAddressInterface::class)
                ->getMockForAbstractClass();
            $cartAddressMock->expects($this->once())
                ->method('getAddressType')
                ->willReturn(Address::TYPE_BILLING);
            $cartMock->expects($this->once())
                ->method('getAddresses')
                ->willReturn([$cartAddressMock]);

            $profileAddressMock = $this->getMockBuilder(ProfileAddressInterface::class)
                ->getMockForAbstractClass();
            $this->addressConverterMock->expects($this->once())
                ->method('fromCartAddress')
                ->with($cartAddressMock)
                ->willReturn($profileAddressMock);
            $this->fullNameResolverMock->expects($this->once())
                ->method('getFullName')
                ->with($cartAddressMock)
                ->willReturn($customerFullName);
            $profileMock->expects($this->once())
                ->method('setAddresses')
                ->with([$profileAddressMock])
                ->willReturnSelf();
        } else {
            $cartMock->expects($this->exactly(2))
                ->method('getIsVirtual')
                ->willReturn($isCartVirtual);
            $cartBillingAddressMock = $this->getMockBuilder(SubscriptionsCartAddressInterface::class)
                ->getMockForAbstractClass();
            $cartBillingAddressMock->expects($this->once())
                ->method('getAddressType')
                ->willReturn(Address::TYPE_BILLING);
            $cartShippingAddressMock = $this->getMockBuilder(SubscriptionsCartAddressInterface::class)
                ->getMockForAbstractClass();
            $cartShippingAddressMock->expects($this->once())
                ->method('getAddressType')
                ->willReturn(Address::TYPE_SHIPPING);
            $cartMock->expects($this->once())
                ->method('getAddresses')
                ->willReturn([$cartBillingAddressMock, $cartShippingAddressMock]);

            $profileBillingAddressMock = $this->getMockBuilder(ProfileAddressInterface::class)
                ->getMockForAbstractClass();
            $profileShippingAddressMock = $this->getMockBuilder(ProfileAddressInterface::class)
                ->getMockForAbstractClass();
            $this->addressConverterMock->expects($this->exactly(2))
                ->method('fromCartAddress')
                ->withConsecutive([$cartBillingAddressMock], [$cartShippingAddressMock])
                ->willReturnOnConsecutiveCalls($profileBillingAddressMock, $profileShippingAddressMock);

            $this->fullNameResolverMock->expects($this->once())
                ->method('getFullName')
                ->with($cartShippingAddressMock)
                ->willReturn($customerFullName);
        }

        $profileMock->expects($this->once())
            ->method('setCustomerFullname')
            ->with($customerFullName)
            ->willReturnSelf();

        $cartMock->expects($this->once())
            ->method('getSubscriptionPlanId')
            ->willReturn($subscriptionPlanId);
        $planMock = $this->getMockBuilder(SubscriptionPlanInterface::class)
            ->getMockForAbstractClass();
        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($subscriptionPlanId)
            ->willReturn($planMock);

        if ($isPlanStartDate) {
            $profileMock->expects($this->once())
                ->method('getStartDate')
                ->willReturn(null);
            $planMock->expects($this->once())
                ->method('getStartDateType')
                ->willReturn($startDateType);
            $planMock->expects($this->once())
                ->method('getStartDateDayOfMonth')
                ->willReturn($dayOfMonth);
            $this->startDateResolverMock->expects($this->once())
                ->method('getStartDate')
                ->with($startDateType, $dayOfMonth)
                ->willReturn($startDate);
            $profileMock->expects($this->once())
                ->method('setStartDate')
                ->with($startDate)
                ->willReturnSelf();
        } else {
            $profileMock->expects($this->once())
                ->method('getStartDate')
                ->willReturn($startDate);
        }

        $planMock->expects($this->once())
            ->method('getEngineCode')
            ->willReturn($engineCode);
        $cartMock->expects($this->once())
            ->method('getPaymentMethodCode')
            ->willReturn($paymentMethodCode);
        $paymentMethodMock = $this->getMockBuilder(PaymentMethodInterface::class)
            ->getMockForAbstractClass();
        $paymentMethodMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($paymentMethodTitle);
        $this->paymentMethodListMock->expects($this->once())
            ->method('getMethod')
            ->with($engineCode, $paymentMethodCode)
            ->willReturn($paymentMethodMock);

        $profileMock->expects($this->once())
            ->method('setPaymentMethodTitle')
            ->with($paymentMethodTitle)
            ->willReturnSelf();

        $this->objectCopyServiceMock->expects($this->exactly(2))
            ->method('copyFieldsetToTarget')
            ->withConsecutive(
                ['aw_sarp_convert_profile', 'from_cart', $cartMock, $profileMock],
                ['aw_sarp_convert_profile', 'from_plan', $planMock, $profileMock]
            )
            ->willReturnSelf();

        $this->assertEquals(
            $profileMock,
            $this->model->fromSubscriptionCart($cartMock)
        );
    }

    /**
     * @return array
     */
    public function fromSubscriptionCartDataProvider()
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];
    }
}
