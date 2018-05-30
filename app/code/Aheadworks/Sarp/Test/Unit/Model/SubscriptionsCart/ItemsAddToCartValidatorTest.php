<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\ItemsAddToCartValidator;
use Aheadworks\Sarp\Model\Product\Type\Restrictions as TypeRestrictions;
use Aheadworks\Sarp\Model\Product\SubscribeAbilityChecker;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\ItemsAddToCartValidator
 */
class ItemsAddToCartValidatorTest extends \PHPUnit\Framework\TestCase
{
    const PRODUCT_ID = 1;

    /**
     * @var ItemsAddToCartValidator
     */
    private $validator;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var TypeRestrictions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeRestrictionsMock;

    /**
     * @var SubscribeAbilityChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscribeAbilityCheckerMock;

    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var array
     */
    private $itemData = [
        'getProductId' => self::PRODUCT_ID,
        'getBuyRequest' => 'a:1:{s:10:"product_id";s:1:"1";}'
    ];

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->typeRestrictionsMock = $this->createMock(TypeRestrictions::class);
        $this->subscribeAbilityCheckerMock = $this->createMock(SubscribeAbilityChecker::class);
        $this->productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $this->validator = $objectManager->getObject(
            ItemsAddToCartValidator::class,
            [
                'productRepository' => $this->productRepositoryMock,
                'typeRestrictions' => $this->typeRestrictionsMock,
                'subscribeAbilityChecker' => $this->subscribeAbilityCheckerMock
            ]
        );
    }

    /**
     * @param SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($itemMock, $expectedResult, $expectedMessages)
    {
        $productType = 'simple';

        $this->productRepositoryMock->expects($this->any())
            ->method('getById')
            ->with(self::PRODUCT_ID)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn($productType);
        $this->typeRestrictionsMock->expects($this->any())
            ->method('getSupportedProductTypes')
            ->willReturn([$productType]);
        $this->subscribeAbilityCheckerMock->expects($this->any())
            ->method('isSubscribeAvailable')
            ->with($this->productMock)
            ->willReturn(true);

        $this->assertEquals($expectedResult, $this->validator->isValid($itemMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    public function testIsValidNotSupportedProductType()
    {
        $productType = 'grouped';
        $supportedProductType = 'simple';
        $expectedMessages = ['Product type %1 isn\'t supported.'];

        $itemMock = $this->createItemMock();

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(self::PRODUCT_ID)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);
        $this->typeRestrictionsMock->expects($this->once())
            ->method('getSupportedProductTypes')
            ->willReturn([$supportedProductType]);

        $this->assertFalse($this->validator->isValid($itemMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    public function testIsValidNonSubscriptionProduct()
    {
        $productType = 'simple';
        $expectedMessages = ['Subscriptions aren\'t allowed for this product.'];

        $itemMock = $this->createItemMock();

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(self::PRODUCT_ID)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);
        $this->typeRestrictionsMock->expects($this->once())
            ->method('getSupportedProductTypes')
            ->willReturn([$productType]);
        $this->subscribeAbilityCheckerMock->expects($this->once())
            ->method('isSubscribeAvailable')
            ->willReturn(false);

        $this->assertFalse($this->validator->isValid($itemMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * Create cart item mock and optionally modify getter result
     *
     * @param string|null $methodModify
     * @param mixed|null $valueModify
     * @return SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createItemMock($methodModify = null, $valueModify = null)
    {
        /** @var SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        foreach ($this->itemData as $method => $value) {
            if ($method != $methodModify) {
                $itemMock->expects($this->any())
                    ->method($method)
                    ->willReturn($value);
            } else {
                $itemMock->expects($this->any())
                    ->method($methodModify)
                    ->willReturn($valueModify);
            }
        }
        return $itemMock;
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            'correct data' => [$this->createItemMock(), true, []],
            'missing product Id' => [
                $this->createItemMock('getProductId', null),
                false,
                ['Product Id is required.']
            ],
            'missing buy request' => [
                $this->createItemMock('getBuyRequest', null),
                false,
                ['Buy request is required.']
            ]
        ];
    }
}
