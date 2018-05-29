<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Block\Checkout;

use Aheadworks\Sarp\Block\Checkout\Success;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Test for \Aheadworks\Sarp\Block\Checkout\Success
 */
class SuccessTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Success
     */
    private $block;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $context = $objectManager->getObject(
            Context::class,
            ['urlBuilder' => $this->urlBuilderMock]
        );
        $this->block = $objectManager->getObject(
            Success::class,
            [
                'customerSession' => $this->customerSessionMock,
                'context' => $context
            ]
        );
    }

    /**
     * @param bool $isLoggedIn
     * @dataProvider boolDataProvider
     */
    public function testIsCustomerLoggedIn($isLoggedIn)
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn($isLoggedIn);
        $this->assertEquals($isLoggedIn, $this->block->isCustomerLoggedIn());
    }

    public function testGetCustomerSubscriptionsUrl()
    {
        $subscriptionsUrl = 'http://localhost/aw_sarp/profile/index';

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('aw_sarp/profile/index')
            ->willReturn($subscriptionsUrl);

        $this->assertEquals($subscriptionsUrl, $this->block->getCustomerSubscriptionsUrl());
    }

    public function testGetCustomerOrdersUrl()
    {
        $ordersUrl = 'http://localhost/sales/order/history';

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('sales/order/history')
            ->willReturn($ordersUrl);

        $this->assertEquals($ordersUrl, $this->block->getCustomerOrdersUrl());
    }

    public function testGetContinueShoppingUrl()
    {
        $continueShoppingUrl = 'http://localhost';

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with(null)
            ->willReturn($continueShoppingUrl);

        $this->assertEquals($continueShoppingUrl, $this->block->getContinueShoppingUrl());
    }

    /**
     * @return array
     */
    public function boolDataProvider()
    {
        return [[true], [false]];
    }
}
