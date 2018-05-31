<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\Product;

use Aheadworks\Sarp\Model\Product\SubscribeAbilityChecker;
use Aheadworks\Sarp\Model\Product\Attribute\Source\SubscriptionType;
use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\Product\SubscribeAbilityChecker
 */
class SubscribeAbilityCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubscribeAbilityChecker
     */
    private $checker;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->checker = $objectManager->getObject(SubscribeAbilityChecker::class);
    }

    /**
     * @param Product|\PHPUnit_Framework_MockObject_MockObject $product
     * @param bool $result
     * @dataProvider isSubscribeAvailableForSimpleDataProvider
     */
    public function testIsSubscribeAvailableForSimple($product, $result)
    {
        $class = new \ReflectionClass($this->checker);
        $method = $class->getMethod('isSubscribeAvailableForSimple');
        $method->setAccessible(true);

        $this->assertEquals($result, $method->invokeArgs($this->checker, [$product]));
    }

    /**
     * @param Product|\PHPUnit_Framework_MockObject_MockObject $product
     * @param bool $result
     * @dataProvider isAddToCartAvailableForSimpleDataProvider
     */
    public function testIsAddToCartAvailableForSimple($product, $result)
    {
        $class = new \ReflectionClass($this->checker);
        $method = $class->getMethod('isAddToCartAvailableForSimple');
        $method->setAccessible(true);

        $this->assertEquals($result, $method->invokeArgs($this->checker, [$product]));
    }

    /**
     * Create product mock
     *
     * @param int $subscriptionType
     * @param float $regularPrice
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createProductMock($subscriptionType, $regularPrice)
    {
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->any())
            ->method('__call')
            ->willReturnMap(
                [
                    ['getAwSarpSubscriptionType', [], $subscriptionType],
                    ['getAwSarpRegularPrice', [], $regularPrice]
                ]
            );
        return $productMock;
    }

    /**
     * @return array
     */
    public function isSubscribeAvailableForSimpleDataProvider()
    {
        return [
            [$this->createProductMock(null, null), false],
            [$this->createProductMock(SubscriptionType::NO, null), false],
            [$this->createProductMock(SubscriptionType::NO, 5), false],
            [$this->createProductMock(SubscriptionType::OPTIONAL, null), false],
            [$this->createProductMock(SubscriptionType::OPTIONAL, 5), true],
            [$this->createProductMock(SubscriptionType::SUBSCRIPTION_ONLY, null), false],
            [$this->createProductMock(SubscriptionType::SUBSCRIPTION_ONLY, 5), true]
        ];
    }

    /**
     * @return array
     */
    public function isAddToCartAvailableForSimpleDataProvider()
    {
        return [
            [$this->createProductMock(null, null), true],
            [$this->createProductMock(SubscriptionType::NO, null), true],
            [$this->createProductMock(SubscriptionType::OPTIONAL, null), true],
            [$this->createProductMock(SubscriptionType::SUBSCRIPTION_ONLY, null), false]
        ];
    }
}
