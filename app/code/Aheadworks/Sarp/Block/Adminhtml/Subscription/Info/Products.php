<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Adminhtml\Subscription\Info;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Products
 * @package Aheadworks\Sarp\Block\Adminhtml\Subscription\Info
 */
class Products extends \Magento\Backend\Block\Template
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp::subscription/info/products.phtml';

    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get profile entity
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set profile entity
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * Check if product exists
     *
     * @param int $productId
     * @return bool
     */
    public function isProductExists($productId)
    {
        try {
            $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return true;
    }

    /**
     * Get product edit url
     *
     * @param int $productId
     * @return string
     */
    public function getProductEditUrl($productId)
    {
        return $this->_urlBuilder->getUrl('catalog/product/edit', ['id' => $productId]);
    }

    /**
     * Format profile item  amount
     *
     * @param float $amount
     * @param string $currencyCode
     * @return float
     */
    public function formatProfileItemAmount($amount, $currencyCode)
    {
        return $this->priceCurrency->format($amount, true, 2, null, $currencyCode);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getProfile()) {
            return '';
        }
        return parent::_toHtml();
    }
}
