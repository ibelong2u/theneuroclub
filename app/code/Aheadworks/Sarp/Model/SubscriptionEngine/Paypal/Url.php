<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;

/**
 * Class Url
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal
 */
class Url
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ConfigProxy
     */
    private $paypalConfigProxy;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param UrlInterface $urlBuilder
     * @param ConfigProxy $paypalConfigProxy
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ConfigProxy $paypalConfigProxy,
        ResolverInterface $localeResolver
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->paypalConfigProxy = $paypalConfigProxy;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Get redirect url for checkout (express checkout start url)
     *
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->urlBuilder->getUrl('aw_sarp/paypalexpress/start');
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->urlBuilder->getUrl('aw_sarp/paypalexpress/return');
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->urlBuilder->getUrl('aw_sarp/paypalexpress/cancel');
    }

    /**
     * Get PayPal start url
     *
     * @param string $token
     * @return string
     */
    public function getPaypalStartUrl($token)
    {
        return $this->paypalConfigProxy->getPayPalBasicStartUrl($token);
    }

    /**
     * Get "What Is PayPal" localized URL
     *
     * @return string
     */
    public function getPaymentMarkWhatIsPayPalUrl()
    {
        $countryCode = \Locale::getRegion($this->localeResolver->getLocale());
        return sprintf(
            'https://www.paypal.com/%s/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside',
            strtolower($countryCode)
        );
    }

    /**
     * Get PayPal "mark" image URL
     *
     * @return string
     */
    public function getPaymentMarkImageUrl()
    {
        return $this->paypalConfigProxy->getPaymentMarkImageUrl($this->localeResolver->getLocale());
    }
}
