<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe
 */
class Config
{
    /**
     * Configuration path to payment is test mode flag
     */
    const XML_PATH_PAYMENT_IS_TEST_MODE = 'payment/aw_sarp_stripe/is_test_mode';

    /**
     * Configuration path to payment test secret key
     */
    const XML_PATH_PAYMENT_TEST_SECRET_KEY = 'payment/aw_sarp_stripe/test_secret_key';

    /**
     * Configuration path to payment test publishable key
     */
    const XML_PATH_PAYMENT_TEST_PUBLISHABLE_KEY = 'payment/aw_sarp_stripe/test_publishable_key';

    /**
     * Configuration path to payment secret key
     */
    const XML_PATH_PAYMENT_SECRET_KEY = 'payment/aw_sarp_stripe/secret_key';

    /**
     * Configuration path to payment publishable key
     */
    const XML_PATH_PAYMENT_PUBLISHABLE_KEY = 'payment/aw_sarp_stripe/publishable_key';

    /**
     * Configuration path to payment credit card types
     */
    const XML_PATH_PAYMENT_CC_TYPES = 'payment/aw_sarp_stripe/cctypes';

    /**
     * Configuration path to payment credit card verification flag
     */
    const XML_PATH_PAYMENT_USE_CCV = 'payment/aw_sarp_stripe/useccv';

    /**
     * Configuration path to payment debug mode flag
     */
    const XML_PATH_PAYMENT_DEBUG = 'payment/aw_sarp_stripe/debug';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if test mode
     *
     * @return bool
     */
    public function isTestMode()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAYMENT_IS_TEST_MODE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get test secret key
     *
     * @return string
     */
    public function getTestSecretKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_TEST_SECRET_KEY,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get test publishable key
     *
     * @return string
     */
    public function getTestPublishableKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_TEST_PUBLISHABLE_KEY,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get secret key
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_SECRET_KEY,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get publishable key
     *
     * @return string
     */
    public function getPublishableKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_PUBLISHABLE_KEY,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get credit card types
     *
     * @return string
     */
    public function getCCTypes()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_CC_TYPES,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Check if use credit card verification
     *
     * @return bool
     */
    public function isUseCcv()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAYMENT_USE_CCV,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if debug on
     *
     * @return bool
     */
    public function isDebugOn()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAYMENT_DEBUG,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
