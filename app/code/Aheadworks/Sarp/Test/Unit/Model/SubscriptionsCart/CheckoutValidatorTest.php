<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\CheckoutValidator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Aheadworks\Sarp\Model\SubscriptionsCart\ConverterManager;
use Magento\Quote\Model\Quote;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\CheckoutValidator
 */
class CheckoutValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CheckoutValidator
     */
    private $validator;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var CheckoutHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutHelperMock;

    /**
     * @var ConverterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converterManagerMock;

    /**
     * @var array
     */
    private $cartData = ['getSubscriptionPlanId' => 1];

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->checkoutHelperMock = $this->createMock(CheckoutHelper::class);
        $this->converterManagerMock = $this->createMock(ConverterManager::class);
        $this->validator = $objectManager->getObject(
            CheckoutValidator::class,
            [
                'productRepository' => $this->productRepositoryMock,
                'customerSession' => $this->customerSessionMock,
                'checkoutHelper' => $this->checkoutHelperMock,
                'converterManager' => $this->converterManagerMock
            ]
        );
    }

    /**
     * @param SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject $cartMock
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($cartMock, $expectedResult, $expectedMessages)
    {
        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->assertEquals($expectedResult, $this->validator->isValid($cartMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * @param string $productType
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidGuestCartDataProvider
     */
    public function testIsValidGuestCart($productType, $expectedResult, $expectedMessages)
    {
        $productId = 2;

        /** @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject $cartMock */
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $quoteMock = $this->createMock(Quote::class);

        $cartMock->expects($this->exactly(2))
            ->method('getItems')
            ->willReturn([$itemMock]);
        $cartMock->expects($this->once())
            ->method('getSubscriptionPlanId')
            ->willReturn(1);
        $this->customerSessionMock->expects($this->exactly(2))
            ->method('isLoggedIn')
            ->willReturn(false);
        $itemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);
        $this->converterManagerMock->expects($this->once())
            ->method('toQuote')
            ->willReturn($quoteMock);
        $this->checkoutHelperMock->expects($this->once())
            ->method('isAllowedGuestCheckout')
            ->with($quoteMock)
            ->willReturn(true);

        $this->assertEquals($expectedResult, $this->validator->isValid($cartMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * Create cart mock and optionally modify getter result
     *
     * @param string|null $methodModify
     * @param mixed|null $valueModify
     * @return SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createCartMock($methodModify = null, $valueModify = null)
    {
        /** @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject $cartMock */
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        foreach ($this->cartData as $method => $value) {
            if ($method != $methodModify) {
                $cartMock->expects($this->any())
                    ->method($method)
                    ->willReturn($value);
            } else {
                $cartMock->expects($this->any())
                    ->method($methodModify)
                    ->willReturn($valueModify);
            }
        }
        if ($methodModify != 'getItems') {
            $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
            $cartMock->expects($this->any())
                ->method('getItems')
                ->willReturn([$itemMock]);
        } else {
            $cartMock->expects($this->any())
                ->method('getItems')
                ->willReturn($valueModify);
        }
        return $cartMock;
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            'correct data' => [$this->createCartMock(), true, []],
            'empty cart' => [$this->createCartMock('getItems', []), false, ['Subscription cart is empty.']],
            'plan is not selected' => [
                $this->createCartMock('getSubscriptionPlanId', null),
                false,
                ['Please select subscription plan.']
            ]
        ];
    }

    /**
     * @return array
     */
    public function isValidGuestCartDataProvider()
    {
        return [
            'simple product' => ['simple', true, []],
            'downloadable product' => [
                'downloadable',
                false,
                ['Guest checkout is not allowed for downloadable products.']
            ]
        ];
    }
}
