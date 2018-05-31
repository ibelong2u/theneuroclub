<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\BuyRequestProcessor;
use Aheadworks\Sarp\Model\SubscriptionsCart\ItemsComparator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\ItemsComparator
 */
class ItemsComparatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ItemsComparator
     */
    private $itemsComparator;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var BuyRequestProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $buyRequestProcessorMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->buyRequestProcessorMock = $this->createMock(BuyRequestProcessor::class);
        $this->itemsComparator = $objectManager->getObject(
            ItemsComparator::class,
            [
                'productRepository' => $this->productRepositoryMock,
                'buyRequestProcessor' => $this->buyRequestProcessorMock
            ]
        );
    }

    /**
     * @param int $product1Id
     * @param Product|\PHPUnit_Framework_MockObject_MockObject $product1Mock
     * @param int $product2Id
     * @param Product|\PHPUnit_Framework_MockObject_MockObject $product2Mock
     * @param bool $expectedResult
     * @dataProvider isEqualsDataProvider
     */
    public function testIsEquals(
        $product1Id,
        $product1Mock,
        $product2Id,
        $product2Mock,
        $expectedResult
    ) {
        $buyRequest1 = 'a:1:{s:10:"product_id";s:1:"1";}';
        $buyRequest2 = 'a:1:{s:10:"product_id";s:1:"2";}';

        /** @var SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject $item1Mock */
        $item1Mock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        /** @var SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject $item2Mock */
        $item2Mock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $item1Mock->expects($this->once())
            ->method('getBuyRequest')
            ->willReturn($buyRequest1);
        $item1Mock->expects($this->once())
            ->method('getProductId')
            ->willReturn($product1Id);
        $item2Mock->expects($this->once())
            ->method('getBuyRequest')
            ->willReturn($buyRequest2);
        $item2Mock->expects($this->once())
            ->method('getProductId')
            ->willReturn($product2Id);

        $this->buyRequestProcessorMock->expects($this->exactly(2))
            ->method('getCartCandidates')
            ->willReturnMap(
                [
                    [$buyRequest1, $product1Id, [$product1Mock]],
                    [$buyRequest2, $product2Id, [$product2Mock]]
                ]
            );

        $this->assertEquals($expectedResult, $this->itemsComparator->isEquals($item1Mock, $item2Mock));
    }

    /**
     * Create product mock
     *
     * @param int $productId
     * @param string $customOptionCode
     * @param string $customOptionValue
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createProductMock($productId, $customOptionCode, $customOptionValue)
    {
        $productMock = $this->createMock(Product::class);
        $customOptionMock = $this->createMock(Option::class);

        $customOptionMock->expects($this->any())
            ->method('__call')
            ->with('getCode')
            ->willReturn($customOptionCode);
        $customOptionMock->expects($this->any())
            ->method('getValue')
            ->willReturn($customOptionValue);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->any())
            ->method('getCustomOptions')
            ->willReturn([$customOptionCode => $customOptionMock]);
        $productMock->expects($this->once())
            ->method('__call')
            ->with('getParentProductId')
            ->willReturn(null);

        return $productMock;
    }

    /**
     * @return array
     */
    public function isEqualsDataProvider()
    {
        return [
            'equals' => [
                1,
                $this->createProductMock(1, 'option', 'value'),
                1,
                $this->createProductMock(1, 'option', 'value'),
                true
            ],
            'different product ids' => [
                1,
                $this->createProductMock(1, 'option', 'value'),
                2,
                $this->createProductMock(2, 'option', 'value'),
                false
            ],
            'different option values' => [
                1,
                $this->createProductMock(1, 'option', 'value1'),
                1,
                $this->createProductMock(1, 'option', 'value2'),
                false
            ],
            'different options' => [
                1,
                $this->createProductMock(1, 'option1', 'value1'),
                1,
                $this->createProductMock(1, 'option2', 'value2'),
                false
            ]
        ];
    }
}
