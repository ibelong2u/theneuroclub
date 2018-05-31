<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Block\Cart;

use Aheadworks\Sarp\Block\Cart\Link;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Test for \Aheadworks\Sarp\Block\Cart\Link
 */
class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Link
     */
    private $block;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $context = $objectManager->getObject(
            Context::class,
            ['urlBuilder' => $this->urlBuilderMock]
        );
        $this->block = $objectManager->getObject(
            Link::class,
            ['context' => $context]
        );
    }

    public function testGetCartUrl()
    {
        $cartUrl = 'http://localhost/aw_sarp/cart/index';

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('aw_sarp/cart/index')
            ->willReturn($cartUrl);

        $this->assertEquals($cartUrl, $this->block->getCartUrl());
    }
}
