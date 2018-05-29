<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block;

use Aheadworks\Sarp\Model\Checkout\CompositeConfigProvider;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Cart
 * @package Aheadworks\Sarp\Block
 */
class Cart extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CompositeConfigProvider
     */
    private $configProvider;

    /**
     * @var Persistor
     */
    private $cartPersistor;

    /**
     * @param Context $context
     * @param CompositeConfigProvider $configProvider
     * @param Persistor $cartPersistor
     * @param array $data
     */
    public function __construct(
        Context $context,
        CompositeConfigProvider $configProvider,
        Persistor $cartPersistor,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->cartPersistor = $cartPersistor;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout'])
            ? $data['jsLayout']
            : [];
    }

    /**
     * Get JS layout
     *
     * @return string
     */
    public function getJsLayout()
    {
        return \Zend_Json::encode($this->jsLayout);
    }

    /**
     * Get subscriptions checkout configuration
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * Get cart items count
     *
     * @return int
     */
    public function getCartItemsCount()
    {
        return count($this->cartPersistor->getSubscriptionCart()->getItems());
    }

    /**
     * Get continue shopping url
     *
     * @return string
     */
    public function getContinueShoppingUrl()
    {
        return $this->_urlBuilder->getUrl();
    }
}
