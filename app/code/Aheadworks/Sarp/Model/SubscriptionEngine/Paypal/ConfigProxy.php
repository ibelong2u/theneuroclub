<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Paypal\Model\Config as PaypalConfig;
use Magento\Paypal\Model\ConfigFactory as PaypalConfigFactory;

/**
 * @SuppressWarnings(PHPMD)
 * Class ConfigProxy
 *
 * @method string getMerchantCountry()
 * @method string getApiCertificate()
 * @method string getBuildNotationCode()
 * @method string getPayPalBasicStartUrl(string $token)
 *
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal
 */
class ConfigProxy implements ConfigInterface
{
    /**
     * @var PaypalConfig
     */
    private $paypalConfig;

    /**
     * @param PaypalConfigFactory $paypalConfigFactory
     */
    public function __construct(PaypalConfigFactory $paypalConfigFactory)
    {
        $this->paypalConfig = $paypalConfigFactory->create();
        $this->paypalConfig->setPathPattern(\Magento\Payment\Gateway\Config\Config::DEFAULT_PATH_PATTERN);
        $this->paypalConfig->setMethodCode('paypal_express');
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($field, $storeId = null)
    {
        return $this->paypalConfig->getValue($field, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethodCode($methodCode)
    {
        $this->paypalConfig->setMethodCode($methodCode);
    }

    /**
     * {@inheritdoc}
     */
    public function setPathPattern($pathPattern)
    {
        $this->paypalConfig->setPathPattern($pathPattern);
    }

    /**
     * \Magento\Paypal\Model\Config methods override
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (method_exists($this->paypalConfig, $method)) {
            return $this->paypalConfig->$method(...array_values($arguments));
        }
        throw new \BadMethodCallException(
            sprintf('Method "%s" does not exist in %s', $method, get_class($this->paypalConfig))
        );
    }
}
