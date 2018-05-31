<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet
 */
class Config
{
    /**
     * Configuration path to payment CC types
     */
    const XML_PATH_PAYMENT_CC_TYPES = 'payment/authorizenet_directpost/cctypes';

    /**
     * Configuration path to payment CC verification
     */
    const XML_PATH_PAYMENT_USE_CCV = 'payment/authorizenet_directpost/useccv';

    /**
     * Configuration path to payment api login
     */
    const XML_PATH_PAYMENT_API_LOGIN = 'payment/authorizenet_directpost/login';

    /**
     * Configuration path to payment transaction key
     */
    const XML_PATH_PAYMENT_TRANS_KEY = 'payment/authorizenet_directpost/trans_key';

    /**
     * Configuration path to payment merchant MD5
     */
    const XML_PATH_PAYMENT_TRANS_MD5 = 'payment/authorizenet_directpost/trans_md5';

    /**
     * Configuration path to payment test mode flag
     */
    const XML_PATH_PAYMENT_TEST_MODE = 'payment/authorizenet_directpost/test';

    /**
     * Configuration path to payment debug flag
     */
    const XML_PATH_PAYMENT_DEBUG = 'payment/authorizenet_directpost/debug';

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
     * Get credit card types
     *
     * @return string
     */
    public function getCCTypes()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_CC_TYPES,
            ScopeInterface::SCOPE_STORE
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
     * Get api login
     *
     * @return string
     */
    public function getApiLoginId()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_API_LOGIN,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get transaction key
     *
     * @return string
     */
    public function getTransactionKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_TRANS_KEY,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get merchant MD5
     *
     * @return string
     */
    public function getMerchantMD5()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_TRANS_MD5,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Check if test mode
     *
     * @return bool
     */
    public function isTestMode()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAYMENT_TEST_MODE,
            ScopeInterface::SCOPE_WEBSITE
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
