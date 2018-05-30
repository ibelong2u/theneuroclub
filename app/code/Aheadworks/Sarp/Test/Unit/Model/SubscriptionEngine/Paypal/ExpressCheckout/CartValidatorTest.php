<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Paypal\ExpressCheckout;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ExpressCheckout\CartValidator;
use Aheadworks\Sarp\Model\SubscriptionsCart\TotalsCollector;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ExpressCheckout\CartValidator
 */
class CartValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CartValidator
     */
    private $validator;

    /**
     * @var array
     */
    private $cartData = [
        'getGrandTotal' => 50.00
    ];

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(CartValidator::class);
    }

    /**
     * @param SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject $cartMock
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($cartMock, $expectedResult, $expectedMessages)
    {
        $this->assertEquals($expectedResult, $this->validator->isValid($cartMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * Create subscription cart mock and optionally modify getter result
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
        if ($methodModify == 'getItems') {
            $cartMock->expects($this->any())
                ->method($methodModify)
                ->willReturn($valueModify);
        } else {
            $cartMock->expects($this->any())
                ->method('getItems')
                ->willReturn(
                    [$this->getMockForAbstractClass(SubscriptionsCartItemInterface::class)]
                );
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
            'empty cart' => [
                $this->createCartMock('getItems', []),
                false,
                ['Subscription cart is empty.']
            ],
            'zero balance' => [
                $this->createCartMock('getGrandTotal', 0),
                false,
                [
                    'PayPal can\'t process subscriptions with a zero balance due. '
                    . 'To finish your purchase, please go through the subscription checkout process.'
                ]
            ],
        ];
    }
}
