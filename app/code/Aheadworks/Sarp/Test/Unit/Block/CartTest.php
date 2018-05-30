<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Block\Cart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Block\Cart;
use Aheadworks\Sarp\Model\Checkout\CompositeConfigProvider;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Test for \Aheadworks\Sarp\Block\Cart
 */
class CartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Cart
     */
    private $block;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var CompositeConfigProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configProviderMock;

    /**
     * @var Persistor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartPersistorMock;

    /**
     * @var array
     */
    private $jsLayout = ['components' => ['cart' => []]];

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->configProviderMock = $this->createMock(CompositeConfigProvider::class);
        $this->cartPersistorMock = $this->createMock(Persistor::class);
        $context = $objectManager->getObject(
            Context::class,
            ['urlBuilder' => $this->urlBuilderMock]
        );
        $this->block = $objectManager->getObject(
            Cart::class,
            [
                'context' => $context,
                'configProvider' => $this->configProviderMock,
                'cartPersistor' => $this->cartPersistorMock,
                'jsLayout' => $this->jsLayout
            ]
        );
    }

    public function testGetJsLayout()
    {
        $jsLayoutEncoded = json_encode($this->jsLayout);
        $this->assertEquals($jsLayoutEncoded, $this->block->getJsLayout());
    }

    public function testGetCheckoutConfig()
    {
        $config = ['subscriptionsCart' => []];

        $this->configProviderMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);

        $this->assertEquals($config, $this->block->getCheckoutConfig());
    }

    public function testGetCartItemsCount()
    {
        $itemsCount = 2;

        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);

        $this->cartPersistorMock->expects($this->once())
            ->method('getSubscriptionCart')
            ->willReturn($cartMock);
        $cartMock->expects($this->once())
            ->method('getItems')
            ->willReturn(
                [
                    $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class),
                    $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class)
                ]
            );

        $this->assertEquals($itemsCount, $this->block->getCartItemsCount());
    }

    public function testGetContinueShoppingUrl()
    {
        $continueShoppingUrl = 'http://localhost';

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($continueShoppingUrl);

        $this->assertEquals($continueShoppingUrl, $this->block->getContinueShoppingUrl());
    }
}
