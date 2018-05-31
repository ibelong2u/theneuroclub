<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Block\Cart;

use Aheadworks\Sarp\Block\Checkout;
use Aheadworks\Sarp\Model\Checkout\CompositeConfigProvider;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Sarp\Block\Checkout\LayoutProcessorProvider;

/**
 * Test for \Aheadworks\Sarp\Block\Checkout
 */
class CheckoutTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Checkout
     */
    private $block;

    /**
     * @var FormKey|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyMock;

    /**
     * @var CompositeConfigProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configProviderMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var LayoutProcessorProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutProviderMock;

    /**
     * @var array
     */
    private $jsLayout = ['components' => ['checkout' => []]];

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->formKeyMock = $this->createMock(FormKey::class);
        $this->configProviderMock = $this->createMock(CompositeConfigProvider::class);
        $this->layoutProviderMock = $this->createMock(LayoutProcessorProvider::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $context = $objectManager->getObject(
            Context::class,
            ['storeManager' => $this->storeManagerMock]
        );
        $this->block = $objectManager->getObject(
            Checkout::class,
            [
                'context' => $context,
                'formKey' => $this->formKeyMock,
                'configProvider' => $this->configProviderMock,
                'layoutProvider' => $this->layoutProviderMock,
                'jsLayout' => $this->jsLayout
            ]
        );
    }

    public function testGetJsLayout()
    {
        $layoutProcessorMock = $this->getMockForAbstractClass(LayoutProcessorInterface::class);

        $isLayoutProcessed = array_merge_recursive(
            $this->jsLayout,
            ['components' => ['checkout' => ['new_component' => []]]]
        );
        $jsLayoutEncoded = json_encode($isLayoutProcessed);
        $layoutProcessorMock->expects($this->once())
            ->method('process')
            ->with($this->jsLayout)
            ->willReturn($isLayoutProcessed);

        $this->layoutProviderMock->expects($this->once())
            ->method('getLayoutProcessors')
            ->willReturn([$layoutProcessorMock]);

        $this->assertEquals($jsLayoutEncoded, $this->block->getJsLayout());
    }

    public function testGetFormKey()
    {
        $formKey = 'form_key_value';
        $this->formKeyMock->expects($this->once())
            ->method('getFormKey')
            ->willReturn($formKey);
        $this->assertEquals($formKey, $this->block->getFormKey());
    }

    public function testGetCheckoutConfig()
    {
        $config = ['subscriptionsCart' => []];
        $this->configProviderMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);
        $this->assertEquals($config, $this->block->getCheckoutConfig());
    }

    public function testGetBaseUrl()
    {
        $baseUrl = 'http://localhost';
        $storeMock = $this->createMock(Store::class);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);
        $this->assertEquals($baseUrl, $this->block->getBaseUrl());
    }
}
