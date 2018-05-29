<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Product;

use Aheadworks\Sarp\Model\Product\RegularPricesConfigProvider;
use Aheadworks\Sarp\Model\Product\SubscribeAbilityChecker;
use Aheadworks\Sarp\Model\Config;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Subscribe
 * @package Aheadworks\Sarp\Block\Product
 */
class Subscribe extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RegularPricesConfigProvider
     */
    private $regularPricesConfigProvider;

    /**
     * @var SubscribeAbilityChecker
     */
    private $subscribeAbilityChecker;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param RegularPricesConfigProvider $regularPricesConfigProvider
     * @param SubscribeAbilityChecker $subscribeAbilityChecker
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        RegularPricesConfigProvider $regularPricesConfigProvider,
        SubscribeAbilityChecker $subscribeAbilityChecker,
        Config $config,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->regularPricesConfigProvider = $regularPricesConfigProvider;
        $this->subscribeAbilityChecker = $subscribeAbilityChecker;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Get product
     *
     * @return ProductInterface|Product
     */
    public function getProduct()
    {
        $productId = $this->getRequest()->getParam('id');
        return $this->productRepository->getById($productId);
    }

    /**
     * Get subscribe url
     *
     * @return string
     */
    public function getSubscribeUrl()
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp/product/subscribe',
            ['product_id' => $this->getProduct()->getId()]
        );
    }

    /**
     * Check if saving estimation enabled
     *
     * @return bool
     */
    public function isSavingEstimationEnabled()
    {
        return $this->config->isDisplayYouSaveXPercentsOnProductPage();
    }

    /**
     * Get prepared tooltip content
     *
     * @return string
     */
    public function getPreparedTooltipContent()
    {
        return preg_replace(
            '#<script[^>]*>.*?</script>#is',
            '',
            $this->config->getTooltipNearSubscriptionButtonContent()
        );
    }

    /**
     * Get regular prices config
     *
     * @return string
     */
    public function getRegularPricesConfig()
    {
        return \Zend_Json::encode(
            $this->regularPricesConfigProvider->getConfig($this->getProduct())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        if ($this->subscribeAbilityChecker->isSubscribeAvailable($this->getProduct())) {
            return parent::toHtml();
        }
        return '';
    }
}
