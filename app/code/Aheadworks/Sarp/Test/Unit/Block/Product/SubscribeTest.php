<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Block\Product;

use Aheadworks\Sarp\Block\Product\Subscribe;
use Aheadworks\Sarp\Model\Product\SubscribeAbilityChecker;
use Aheadworks\Sarp\Model\Config;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Test for \Aheadworks\Sarp\Block\Product\Subscribe
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscribeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Subscribe
     */
    private $block;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var SubscribeAbilityChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscribeAbilityCheckerMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->subscribeAbilityCheckerMock = $this->createMock(SubscribeAbilityChecker::class);
        $this->configMock = $this->createMock(Config::class);
        $context = $objectManager->getObject(
            Context::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'request' => $this->requestMock
            ]
        );
        $this->block = $objectManager->getObject(
            Subscribe::class,
            [
                'context' => $context,
                'productRepository' => $this->productRepositoryMock,
                'subscribeAbilityChecker' => $this->subscribeAbilityCheckerMock,
                'config' => $this->configMock
            ]
        );
    }

    /**
     * Set up mocks for getProduct() method
     *
     * @param ProductInterface|\PHPUnit_Framework_MockObject_MockObject $productMock
     * @param int|null $productId
     */
    private function setUpGetProduct($productMock, $productId = null)
    {
        if (!$productId) {
            $productId = 1;
        }
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($productId);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);
    }

    public function testGetProduct()
    {
        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $this->setUpGetProduct($productMock);
        $this->assertEquals($productMock, $this->block->getProduct());
    }

    public function testGetSubscribeUrl()
    {
        $productId = 1;
        $subscribeUrl = 'http://localhost/aw_sarp/product/subscribe/product_id/1';

        $productMock = $this->getMockForAbstractClass(ProductInterface::class);

        $this->setUpGetProduct($productMock, $productId);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('aw_sarp/product/subscribe', ['product_id' => $productId])
            ->willReturn($subscribeUrl);

        $this->assertEquals($subscribeUrl, $this->block->getSubscribeUrl());
    }

    public function testIsSavingEstimationEnabled()
    {
        $result = true;

        $this->configMock->expects($this->once())
            ->method('isDisplayYouSaveXPercentsOnProductPage')
            ->willReturn($result);

        $this->assertEquals($result, $this->block->isSavingEstimationEnabled());
    }

    public function testToHtml()
    {
        $productMock = $this->getMockForAbstractClass(ProductInterface::class);

        $this->setUpGetProduct($productMock);
        $this->subscribeAbilityCheckerMock->expects($this->once())
            ->method('isSubscribeAvailable')
            ->with($productMock)
            ->willReturn(false);

        $this->assertEmpty($this->block->toHtml());
    }
}
