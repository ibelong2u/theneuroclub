<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe;

use Aheadworks\Sarp\Model\Checkout\ConfigProviderInterface;
use Magento\Payment\Model\CcConfig;

/**
 * Class CheckoutConfig
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe
 */
class CheckoutConfig implements ConfigProviderInterface
{
    /**
     * Method code
     */
    const METHOD_CODE = 'stripe';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CcConfig
     */
    private $ccConfig;

    /**
     * @param Config $config
     * @param CcConfig $ccConfig
     */
    public function __construct(
        Config $config,
        CcConfig $ccConfig
    ) {
        $this->config = $config;
        $this->ccConfig = $ccConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'ccform' => [
                    'availableTypes' => [self::METHOD_CODE => $this->getAvailableCCTypes()],
                    'months' => [self::METHOD_CODE => $this->ccConfig->getCcMonths()],
                    'years' => [self::METHOD_CODE => $this->ccConfig->getCcYears()],
                    'hasVerification' => [self::METHOD_CODE => $this->config->isUseCcv()],
                    'cvvImageUrl' => [self::METHOD_CODE => $this->ccConfig->getCvvImageUrl()]
                ],
                'stripe' => [
                    'publishableKey' => $this->getPublishableKey()
                ]
            ]
        ];
    }

    /**
     * Get available CC types
     *
     * @return array
     */
    private function getAvailableCCTypes()
    {
        $result = [];
        $allTypes = $this->ccConfig->getCcAvailableTypes();
        $availableTypes = $this->config->getCCTypes();
        if ($availableTypes) {
            $availableTypes = explode(',', $availableTypes);
            foreach ($availableTypes as $ccType) {
                if (array_key_exists($ccType, $allTypes)) {
                    $result[$ccType] = $allTypes[$ccType];
                }
            }
        } else {
            $result = $allTypes;
        }
        return $result;
    }

    /**
     * Get publishable key
     *
     * @return string
     */
    private function getPublishableKey()
    {
        $isTestMode = $this->config->isTestMode();
        return $isTestMode
            ? $this->config->getTestPublishableKey()
            : $this->config->getPublishableKey();
    }
}
