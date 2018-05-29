<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Aheadworks\Sarp\Model
 */
class Config
{
    /**
     * Configuration path to apply tax on trial amount
     */
    const XML_PATH_APPLY_TAX_ON_TRIAL_AMOUNT = 'aw_sarp/general/apply_tax_on_trial_amount';

    /**
     * Configuration path to apply tax on shipping amount
     */
    const XML_PATH_APPLY_TAX_ON_SHIPPING_AMOUNT = 'aw_sarp/general/apply_tax_on_shipping_amount';

    /**
     * Configuration path to display "You Save X %" on product page
     */
    const XML_PATH_DISPLAY_YOU_SAVE_X_PERCENTS = 'aw_sarp/general/display_you_save_x_percents_on_product_page';

    /**
     * Configuration path to tooltip near subscription button on product page
     */
    const XML_PATH_TOOLTIP_NEAR_SUBSCRIPTION_BUTTON = 'aw_sarp/general/tooltip_near_subscription_button_content';

    /**
     * Configuration path to keep log for days
     */
    const XML_PATH_KEEP_LOG_FOR_DAYS = 'aw_sarp/general/keep_log_for_days';

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
     * Check if apply tax on trial amount
     *
     * @return bool
     */
    public function isApplyTaxOnTrialAmount()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_APPLY_TAX_ON_TRIAL_AMOUNT,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Check if apply tax on shipping amount
     *
     * @return bool
     */
    public function isApplyTaxOnShippingAmount()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_APPLY_TAX_ON_SHIPPING_AMOUNT,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Check if display "You Save X %" on Product Page
     *
     * @return bool
     */
    public function isDisplayYouSaveXPercentsOnProductPage()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_YOU_SAVE_X_PERCENTS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get content of tooltip near subscription button on product page
     *
     * @return string
     */
    public function getTooltipNearSubscriptionButtonContent()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TOOLTIP_NEAR_SUBSCRIPTION_BUTTON,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve config value for keeping log records
     *
     * @return int
     */
    public function getKeepLogForDaysValue()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_KEEP_LOG_FOR_DAYS
        );
    }
}
