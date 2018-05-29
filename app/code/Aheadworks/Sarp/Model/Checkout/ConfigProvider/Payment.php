<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Checkout\ConfigProvider;

use Aheadworks\Sarp\Api\PaymentMethodManagementInterface;
use Aheadworks\Sarp\Model\Checkout\ConfigProviderInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\CheckoutConfigFactory;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\CcConfigProvider;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Class Payment
 * @package Aheadworks\Sarp\Model\Checkout\ConfigProvider
 */
class Payment implements ConfigProviderInterface
{
    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var CheckoutConfigFactory
     */
    private $checkoutConfigFactory;

    /**
     * @var Persistor
     */
    private $cartPersistor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CcConfigProvider
     */
    private $ccConfigProvider;

    /**
     * @param EngineMetadataPool $engineMetadataPool
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CheckoutConfigFactory $checkoutConfigFactory
     * @param Persistor $cartPersistor
     * @param ScopeConfigInterface $scopeConfig
     * @param CcConfigProvider $ccConfigProvider
     */
    public function __construct(
        EngineMetadataPool $engineMetadataPool,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CheckoutConfigFactory $checkoutConfigFactory,
        Persistor $cartPersistor,
        ScopeConfigInterface $scopeConfig,
        CcConfigProvider $ccConfigProvider
    ) {
        $this->engineMetadataPool = $engineMetadataPool;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->checkoutConfigFactory = $checkoutConfigFactory;
        $this->cartPersistor = $cartPersistor;
        $this->scopeConfig = $scopeConfig;
        $this->ccConfigProvider = $ccConfigProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return array_merge_recursive(
            $this->getEnginesConfigData(),
            [
                'paymentMethods' => $this->getPaymentMethods(),
                'reloadOnBillingAddress' => $this->isReloadOnBillingAddress(),
                'payment' => [
                    'ccform' => [
                        'icons' => $this->ccConfigProvider->getIcons()
                    ]
                ]
            ]
        );
    }

    /**
     * Get engine specific config data
     *
     * @return array
     * @throws \Exception
     */
    private function getEnginesConfigData()
    {
        $configData = [];
        foreach ($this->engineMetadataPool->getEnginesCodes() as $enginesCode) {
            $engineMetadata = $this->engineMetadataPool->getMetadata($enginesCode);
            $configClassName = $engineMetadata->getCheckoutConfigClassName();
            if ($configClassName) {
                $config = $this->checkoutConfigFactory->create($configClassName);
                $configData = array_merge_recursive($configData, $config->getConfig());
            }
        }
        return $configData;
    }

    /**
     * Get payment methods
     *
     * @return array
     */
    private function getPaymentMethods()
    {
        $paymentMethodsData = [];
        $cartId = $this->cartPersistor->getCartId();
        foreach ($this->paymentMethodManagement->getList($cartId) as $paymentMethod) {
            $paymentMethodsData[] = [
                'code' => $paymentMethod->getCode(),
                'title' => __($paymentMethod->getTitle())
            ];
        }
        return $paymentMethodsData;
    }

    /**
     * Check if reload on billing address
     *
     * @return bool
     */
    private function isReloadOnBillingAddress()
    {
        $taxCalculationBasedOn = $this->scopeConfig->getValue(
            TaxConfig::CONFIG_XML_PATH_BASED_ON,
            ScopeInterface::SCOPE_STORE
        );
        $cart = $this->cartPersistor->getSubscriptionCart();
        return $taxCalculationBasedOn == 'billing' || $cart->getIsVirtual();
    }
}
