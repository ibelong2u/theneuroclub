<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Paypal;

use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ConfigProxy;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Url;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Url
 */
class UrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Url
     */
    private $url;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var ConfigProxy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paypalConfigProxyMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->paypalConfigProxyMock = $this->createPartialMock(ConfigProxy::class, ['getPayPalBasicStartUrl']);
        $this->url = $objectManager->getObject(
            Url::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'paypalConfigProxy' => $this->paypalConfigProxyMock
            ]
        );
    }

    public function testGetCheckoutRedirectUrl()
    {
        $url = 'http://localhost/aw_sarp/paypalexpress/start';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('aw_sarp/paypalexpress/start')
            ->willReturn($url);
        $this->assertEquals($url, $this->url->getCheckoutRedirectUrl());
    }

    public function testGetReturnUrl()
    {
        $returnUrl = 'http://localhost/aw_sarp/paypalexpress/return';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('aw_sarp/paypalexpress/return')
            ->willReturn($returnUrl);
        $this->assertEquals($returnUrl, $this->url->getReturnUrl());
    }

    public function testGetCancelUrl()
    {
        $returnUrl = 'http://localhost/aw_sarp/paypalexpress/cancel';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('aw_sarp/paypalexpress/cancel')
            ->willReturn($returnUrl);
        $this->assertEquals($returnUrl, $this->url->getCancelUrl());
    }

    public function testGetPaypalStartUrl()
    {
        $token = 'token_value';
        $paypalStartUrl = 'https://www.paypal.com';
        $this->paypalConfigProxyMock->expects($this->once())
            ->method('getPayPalBasicStartUrl')
            ->with($token)
            ->willReturn($paypalStartUrl);
        $this->assertEquals($paypalStartUrl, $this->url->getPaypalStartUrl($token));
    }
}
