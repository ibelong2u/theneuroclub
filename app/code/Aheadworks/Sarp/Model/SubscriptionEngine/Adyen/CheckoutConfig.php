<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Adyen;

use Aheadworks\Sarp\Model\Checkout\ConfigProviderInterface;
use Magento\Framework\Config\DataInterface;
use Magento\Payment\Model\CcConfig;

/**
 * Class CheckoutConfig
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Adyen
 */
class CheckoutConfig implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DataInterface
     */
    private $dataStorage;

    /**
     * @var CcConfig
     */
    private $ccConfig;

    /**
     * @param Config $config
     * @param DataInterface $dataStorage
     * @param CcConfig $ccConfig
     */
    public function __construct(
        Config $config,
        DataInterface $dataStorage,
        CcConfig $ccConfig
    ) {
        $this->config = $config;
        $this->dataStorage = $dataStorage;
        $this->ccConfig = $ccConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'ccform' => $this->getCCFormConfig('adyen_cc')
            ]
        ];
    }

    /**
     * @param string $methodCode
     * @return array
     */
    private function getCCFormConfig($methodCode)
    {
        $config = [
            'availableTypes' => [$methodCode =>$this->getAvailableCCTypes($methodCode)],
            'months' => [$methodCode => $this->ccConfig->getCcMonths()],
            'years' => [$methodCode => $this->ccConfig->getCcYears()],
            'hasVerification' => [$methodCode => $this->config->getConfigData('cse_enabled', $methodCode)],
            'cvvImageUrl' => [$methodCode => $this->ccConfig->getCvvImageUrl()],
            'generationTime' => [$methodCode => date('c')]
        ];

        $isDemoMode = (bool)$this->config->getConfigData('demo_mode', 'adyen_abstract');
        $cseKey = $isDemoMode
            ? $this->config->getConfigData('cse_publickey_test', $methodCode)
            : $this->config->getConfigData('cse_publickey_live', $methodCode);
        $config['cseKey'] = [$methodCode => $cseKey];

        return $config;
    }

    /**
     * Get available CC types
     *
     * @param string $methodCode
     * @return array
     */
    private function getAvailableCCTypes($methodCode)
    {
        $result = [];
        $allTypes = $this->dataStorage->get('adyen_credit_cards');
        $availableTypes = $this->config->getConfigData('cctypes', $methodCode);
        if ($availableTypes) {
            $availableTypes = explode(',', $availableTypes);
            foreach (array_keys($allTypes) as $code) {
                if (in_array($code, $availableTypes)) {
                    $result[$code] = $allTypes[$code]['name'];
                }
            }
        }
        return $result;
    }
}
