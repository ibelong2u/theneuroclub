<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Product;

use Aheadworks\Sarp\Model\Product\Attribute\Source\SubscriptionType;
use Magento\Bundle\Api\ProductLinkManagementInterface as BundleProductLinkManagement;
use Magento\Bundle\Api\ProductOptionRepositoryInterface as BundleProductOptionRepository;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Api\LinkManagementInterface as ConfigurableLinkManagement;

/**
 * Class SubscribeAbilityChecker
 * @package Aheadworks\Sarp\Model\Product
 */
class SubscribeAbilityChecker
{
    /**
     * @var BundleProductLinkManagement
     */
    private $bundleProductLinkManagement;

    /**
     * @var BundleProductOptionRepository
     */
    private $bundleProductOptionRepository;

    /**
     * @var ConfigurableLinkManagement
     */
    private $configurableProductLinkManagement;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var array
     */
    private $checkCache = [];

    /**
     * @param BundleProductLinkManagement $bundleProductLinkManagement
     * @param BundleProductOptionRepository $bundleProductOptionRepository
     * @param ConfigurableLinkManagement $configurableProductLinkManagement
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        BundleProductLinkManagement $bundleProductLinkManagement,
        BundleProductOptionRepository $bundleProductOptionRepository,
        ConfigurableLinkManagement $configurableProductLinkManagement,
        ProductRepositoryInterface $productRepository
    ) {
        $this->bundleProductLinkManagement = $bundleProductLinkManagement;
        $this->bundleProductOptionRepository = $bundleProductOptionRepository;
        $this->configurableProductLinkManagement = $configurableProductLinkManagement;
        $this->productRepository = $productRepository;
    }

    /**
     * Check if subscribe action available for product
     *
     * @param ProductInterface $product
     * @return bool
     */
    public function isSubscribeAvailable($product)
    {
        return $this->invokeCheck($product, 'isSubscribeAvailableForSimple');
    }

    /**
     * Check if subscribe action available for product with specified product ID
     *
     * @param int $productId
     * @return bool
     */
    public function isSubscribeAvailableByProductId($productId)
    {
        $product = $this->productRepository->getById($productId);
        return $this->isSubscribeAvailable($product);
    }

    /**
     * Check if add to cart action available for product
     *
     * @param ProductInterface $product
     * @return bool
     */
    public function isAddToCartAvailable($product)
    {
        return $this->invokeCheck($product, 'isAddToCartAvailableForSimple');
    }

    /**
     * Check if subscribe action available for simple product
     *
     * @param ProductInterface $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function isSubscribeAvailableForSimple($product)
    {
        return $product->getAwSarpSubscriptionType()
            && $product->getAwSarpSubscriptionType() != SubscriptionType::NO
            && $product->getAwSarpRegularPrice();
    }

    /**
     * Check if add to cart action available for simple product
     *
     * @param ProductInterface $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function isAddToCartAvailableForSimple($product)
    {
        if (!$product->getAwSarpSubscriptionType()) {
            return true;
        }
        return $product->getAwSarpSubscriptionType() != SubscriptionType::SUBSCRIPTION_ONLY;
    }

    /**
     * Invoke check
     *
     * @param ProductInterface $product
     * @param string $methodName
     * @return bool
     */
    private function invokeCheck($product, $methodName)
    {
        $key = $product->getSku() . '-' . $methodName;
        if (!isset($this->checkCache[$key])) {
            $checkResult = false;
            if (in_array($product->getTypeId(), ['simple', 'virtual', 'downloadable'])) {
                $checkResult = $this->$methodName($product);
            } elseif ($product->getTypeId() == 'bundle') {
                $checkResult = $this->invokeForBundle($product, $methodName);
            } elseif ($product->getTypeId() == 'configurable') {
                $checkResult = $this->invokeForConfigurable($product, $methodName);
            }
            $this->checkCache[$key] = $checkResult;
        }
        return $this->checkCache[$key];
    }

    /**
     * Invoke method for bundle product
     *
     * @param ProductInterface $product
     * @param string $methodName
     * @return bool
     */
    private function invokeForBundle($product, $methodName)
    {
        $bundleOptions = $this->bundleProductOptionRepository->getList($product->getSku());
        foreach ($bundleOptions as $option) {
            if ($option->getRequired()) {
                $isPerformedForBundleOption = false;
                foreach ($option->getProductLinks() as $productLink) {
                    $childProduct = $this->productRepository->get($productLink->getSku());
                    if ($this->$methodName($childProduct)) {
                        $isPerformedForBundleOption = true;
                        break;
                    }
                }
                if (!$isPerformedForBundleOption) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Invoke method for configurable product
     *
     * @param ProductInterface|\Magento\Catalog\Model\Product $product
     * @param string $methodName
     * @return bool
     */
    private function invokeForConfigurable($product, $methodName)
    {
        $childProducts = $this->configurableProductLinkManagement->getChildren($product->getData('sku'));
        foreach ($childProducts as $childProduct) {
            $childProduct = $this->productRepository->get($childProduct->getSku());
            if ($this->$methodName($childProduct)) {
                return true;
            }
        }
        return false;
    }
}
