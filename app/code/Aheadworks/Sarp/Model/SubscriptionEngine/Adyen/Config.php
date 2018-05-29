<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Adyen;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Adyen
 */
class Config
{
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
     * Get payment config data
     *
     * @param string $field
     * @param string $group
     * @param int|null $storeId
     * @return mixed
     */
    public function getConfigData($field, $group, $storeId = null)
    {
        $pathPattern = 'payment/%s/%s';
        return $this->scopeConfig->getValue(
            sprintf($pathPattern, $group, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
