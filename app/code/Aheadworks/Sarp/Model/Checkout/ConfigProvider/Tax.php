<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Checkout\ConfigProvider;

use Aheadworks\Sarp\Model\Checkout\ConfigProviderInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Tax
 * @package Aheadworks\Sarp\Model\Checkout\ConfigProvider
 */
class Tax implements ConfigProviderInterface
{
    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param TaxConfig $taxConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        TaxConfig $taxConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->taxConfig = $taxConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'isDisplayShippingPriceExclTax' => $this->isDisplayShippingPriceExclTax(),
            'isDisplayShippingBothPrices' => $this->isDisplayShippingBothPrices(),
            'defaultCountryId' => $this->getDefaultCountryId(),
            'defaultRegionId' => $this->getDefaultRegionId(),
            'defaultPostcode' => $this->getDefaultPostcode()
        ];
    }

    /**
     * Check if display shipping price excluding tax
     *
     * @return bool
     */
    private function isDisplayShippingPriceExclTax()
    {
        return $this->taxConfig->getShippingPriceDisplayType() == TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * Check if display shipping both prices
     *
     * @return bool
     */
    private function isDisplayShippingBothPrices()
    {
        return $this->taxConfig->getShippingPriceDisplayType() == TaxConfig::DISPLAY_TYPE_BOTH;
    }

    /**
     * Get default country Id
     *
     * @return string
     */
    private function getDefaultCountryId()
    {
        return $this->scopeConfig->getValue(
            TaxConfig::CONFIG_XML_PATH_DEFAULT_COUNTRY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get default region Id
     *
     * @return int|null
     */
    private function getDefaultRegionId()
    {
        $defaultRegionId = $this->scopeConfig->getValue(
            TaxConfig::CONFIG_XML_PATH_DEFAULT_REGION,
            ScopeInterface::SCOPE_STORE
        );
        return $defaultRegionId != 0 ? $defaultRegionId : null;
    }

    /**
     * Get default postcode
     *
     * @return string
     */
    private function getDefaultPostcode()
    {
        return $this->scopeConfig->getValue(
            TaxConfig::CONFIG_XML_PATH_DEFAULT_POSTCODE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
