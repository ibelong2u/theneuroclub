<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Paypal;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataResolver;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp as ApiNvp;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ConfigProxy;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ExpressCheckout;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ExpressCheckout\CartValidator;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Url;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor as CartPersistor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ExpressCheckout
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExpressCheckoutTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ExpressCheckout
     */
    private $expressCheckout;

    /**
     * @var ApiNvp|\PHPUnit_Framework_MockObject_MockObject
     */
    private $apiMock;

    /**
     * @var CartPersistor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartPersistorMock;

    /**
     * @var SubscriptionsCartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var CartValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartValidatorMock;

    /**
     * @var ConfigProxy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configProxyMock;

    /**
     * @var DataResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineDataResolverMock;

    /**
     * @var Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectFactoryMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessorMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->apiMock = $this->createMock(ApiNvp::class);
        $this->cartPersistorMock = $this->createMock(CartPersistor::class);
        $this->cartRepositoryMock = $this->getMockForAbstractClass(SubscriptionsCartRepositoryInterface::class);
        $this->cartValidatorMock = $this->createMock(CartValidator::class);
        $this->configProxyMock = $this->createPartialMock(ConfigProxy::class, ['getMerchantCountry', 'getValue']);
        $this->engineDataResolverMock = $this->createMock(DataResolver::class);
        $this->urlMock = $this->createMock(Url::class);
        $this->dataObjectFactoryMock = $this->createMock(DataObjectFactory::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->dataObjectProcessorMock = $this->createMock(DataObjectProcessor::class);
        $this->expressCheckout = $objectManager->getObject(
            ExpressCheckout::class,
            [
                'api' => $this->apiMock,
                'cartPersistor' => $this->cartPersistorMock,
                'cartRepository' => $this->cartRepositoryMock,
                'cartValidator' => $this->cartValidatorMock,
                'paypalConfigProxy' => $this->configProxyMock,
                'engineDataResolver' => $this->engineDataResolverMock,
                'url' => $this->urlMock,
                'dataObjectFactory' => $this->dataObjectFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock
            ]
        );
    }

    public function testStart()
    {
        $token = 'token_value';
        $currencyCode = 'USD';
        $solutionType = 'Mark';
        $returnUrl = 'http://localhost/aw_sarp/paypalexpress/return';
        $cancelUrl = 'http://localhost/aw_sarp/paypalexpress/cancel';
        $isCartVirtual = false;
        $profileDescription = 'Recurring profile for product: Product Name';
        $shippingAddressData = ['address_type' => 'shipping'];
        $billingAddressData = ['address_type' => 'billing'];

        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $requestMock = $this->createMock(DataObject::class);
        $responseMock = $this->createMock(DataObject::class);
        $shippingAddressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);

        $this->cartPersistorMock->expects($this->once())
            ->method('getSubscriptionCart')
            ->willReturn($cartMock);
        $this->cartValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($cartMock)
            ->willReturn(true);
        $this->dataObjectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($requestMock);
        $this->configProxyMock->expects($this->once())
            ->method('getMerchantCountry')
            ->willReturn('US');
        $this->configProxyMock->expects($this->once())
            ->method('getValue')
            ->with('solutionType')
            ->willReturn($solutionType);
        $cartMock->expects($this->once())
            ->method('getCartCurrencyCode')
            ->willReturn($currencyCode);
        $this->urlMock->expects($this->once())
            ->method('getReturnUrl')
            ->willReturn($returnUrl);
        $this->urlMock->expects($this->once())
            ->method('getCancelUrl')
            ->willReturn($cancelUrl);
        $cartMock->expects($this->exactly(3))
            ->method('getIsVirtual')
            ->willReturn($isCartVirtual);
        $this->engineDataResolverMock->expects($this->once())
            ->method('getProfileDescriptionUsingCart')
            ->with($cartMock)
            ->willReturn($profileDescription);
        $cartMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$shippingAddressMock, $billingAddressMock]);
        $this->dataObjectProcessorMock->expects($this->exactly(2))
            ->method('buildOutputDataArray')
            ->withConsecutive(
                [$shippingAddressMock, SubscriptionsCartAddressInterface::class],
                [$billingAddressMock, SubscriptionsCartAddressInterface::class]
            )
            ->willReturnOnConsecutiveCalls($shippingAddressData, $billingAddressData);
        $shippingAddressMock->expects($this->once())
            ->method('getAddressType')
            ->willReturn(Address::TYPE_SHIPPING);
        $billingAddressMock->expects($this->once())
            ->method('getAddressType')
            ->willReturn(Address::TYPE_BILLING);
        $requestMock->expects($this->any())
            ->method('__call')
            ->withConsecutive(
                ['setCurrencyCode', [$currencyCode]],
                ['setReturnUrl', [$returnUrl]],
                ['setCancelUrl', [$cancelUrl]],
                ['setSolutionType', [$solutionType]],
                ['setSuppressShipping', [$isCartVirtual]],
                ['setBillingType', ['RecurringPayments']],
                ['setBillingAgreementDescription', [$profileDescription]],
                ['setShippingAddress', [$shippingAddressData]],
                ['setBillingAddress', [$billingAddressData]]
            )
            ->willReturnSelf();
        $this->apiMock->expects($this->once())
            ->method('callSetExpressCheckout')
            ->willReturn($responseMock);
        $responseMock->expects($this->once())
            ->method('__call')
            ->with('getToken')
            ->willReturn($token);

        $this->assertEquals($token, $this->expressCheckout->start());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Subscription cart is not valid.
     */
    public function testStartCartNotValid()
    {
        $errorMessage = 'Subscription cart is not valid.';

        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);

        $this->cartPersistorMock->expects($this->once())
            ->method('getSubscriptionCart')
            ->willReturn($cartMock);
        $this->cartValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($cartMock)
            ->willReturn(false);
        $this->cartValidatorMock->expects($this->once())
            ->method('getMessages')
            ->willReturn([$errorMessage]);

        $this->expressCheckout->start();
    }

    public function testUpdateCart()
    {
        $token = 'token_value';
        $billingAddressData = ['address_type' => 'billing'];

        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $requestMock = $this->createMock(DataObject::class);
        $responseMock = $this->createMock(DataObject::class);
        $shippingAddressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);

        $this->cartPersistorMock->expects($this->once())
            ->method('getSubscriptionCart')
            ->willReturn($cartMock);
        $this->dataObjectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($requestMock);
        $requestMock->expects($this->once())
            ->method('__call')
            ->with('setToken', [$token]);
        $this->apiMock->expects($this->once())
            ->method('callGetExpressCheckoutDetails')
            ->with($requestMock)
            ->willReturn($responseMock);
        $cartMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$shippingAddressMock, $billingAddressMock]);
        $shippingAddressMock->expects($this->once())
            ->method('getAddressType')
            ->willReturn(Address::TYPE_SHIPPING);
        $billingAddressMock->expects($this->once())
            ->method('getAddressType')
            ->willReturn(Address::TYPE_BILLING);
        $responseMock->expects($this->once())
            ->method('__call')
            ->with('getBillingAddress')
            ->willReturn($billingAddressData);
        $billingAddressMock->expects($this->once())
            ->method('setCustomerAddressId')
            ->with(null);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with(
                $billingAddressMock,
                $billingAddressData,
                SubscriptionsCartAddressInterface::class
            );
        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($cartMock, false);

        $this->expressCheckout->updateCart($token);
    }
}
