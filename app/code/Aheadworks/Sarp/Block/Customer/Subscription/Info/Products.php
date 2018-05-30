<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Customer\Subscription\Info;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Products
 * @package Aheadworks\Sarp\Block\Customer\Subscription\Info
 */
class Products extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductUrl
     */
    private $productUrl;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param ProductUrl $productUrl
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        ProductUrl $productUrl,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileRepository = $profileRepository;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->productUrl = $productUrl;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get profile ID
     *
     * @return int|null
     */
    private function getProfileId()
    {
        return $this->getRequest()->getParam('profile_id');
    }

    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profileRepository->get($this->getProfileId());
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
     * Check if product has url
     *
     * @param int $productId
     * @return bool
     */
    public function hasProductUrl($productId)
    {
        /** @var ProductInterface|Product $product */
        $product = $this->productRepository->getById($productId);
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get product url
     *
     * @param int $productId
     * @return string
     */
    public function getProductUrl($productId)
    {
        /** @var ProductInterface|Product $product */
        $product = $this->productRepository->getById($productId);
        return $this->productUrl->getUrl($product);
    }

    /**
     * Format profile item amount
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
        if (!$this->getProfileId() || !$this->customerSession->isLoggedIn()) {
            return '';
        }
        return parent::_toHtml();
    }
}
