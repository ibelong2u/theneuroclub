<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal
 */
class Config
{
    /**
     * Configuration path to payment merchant timezone
     */
    const XML_PATH_PAYMENT_MERCHANT_TIMEZONE = 'payment/account/merchant_timezone';

    /**
     * Configuration path to default timezone
     */
    const XML_PATH_DEFAULT_TIMEZONE = 'general/locale/timezone';

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
     * Get merchant timezone config value
     *
     * @return string
     */
    public function getMerchantTimezone()
    {
        $value =  $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_MERCHANT_TIMEZONE,
            ScopeInterface::SCOPE_WEBSITE
        );
        if (!$value) {
            return $this->scopeConfig->getValue(
                self::XML_PATH_DEFAULT_TIMEZONE,
                ScopeInterface::SCOPE_WEBSITE
            );
        }
        return $value;
    }
}
