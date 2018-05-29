<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block;

use Aheadworks\Sarp\Model\Checkout\CompositeConfigProvider;
use Aheadworks\Sarp\Block\Checkout\LayoutProcessorProvider;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Checkout
 * @package Aheadworks\Sarp\Block
 */
class Checkout extends \Magento\Framework\View\Element\Template
{
    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var CompositeConfigProvider
     */
    private $configProvider;

    /**
     * @var LayoutProcessorProvider
     */
    private $layoutProvider;

    /**
     * @param Context $context
     * @param FormKey $formKey
     * @param CompositeConfigProvider $configProvider
     * @param LayoutProcessorProvider $layoutProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        CompositeConfigProvider $configProvider,
        LayoutProcessorProvider $layoutProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->_isScopePrivate = true;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout'])
            ? $data['jsLayout']
            : [];
        $this->configProvider = $configProvider;
        $this->layoutProvider = $layoutProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsLayout()
    {
        return \Zend_Json::encode($this->processJsLayout());
    }

    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Get checkout configuration
     *
     * @return array
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Process js layout
     *
     * @return array
     */
    private function processJsLayout()
    {
        $jsLayout = $this->jsLayout;
        foreach ($this->layoutProvider->getLayoutProcessors() as $processor) {
            $jsLayout = $processor->process($jsLayout);
        }
        return $jsLayout;
    }
}
