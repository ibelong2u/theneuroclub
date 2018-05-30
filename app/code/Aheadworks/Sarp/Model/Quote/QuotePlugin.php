<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Quote;

use Aheadworks\Sarp\Model\Product\Attribute\Source\SubscriptionType;
use Magento\Quote\Model\Quote;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\Bundle\Api\ProductOptionRepositoryInterface as BundleProductOptionRepository;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class QuotePlugin
 * @package Aheadworks\Sarp\Model\Quote
 */
class QuotePlugin
{
    /**
     * @var BundleProductOptionRepository
     */
    private $bundleProductOptionRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param BundleProductOptionRepository $bundleProductOptionRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        BundleProductOptionRepository $bundleProductOptionRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->bundleProductOptionRepository = $bundleProductOptionRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Disable add to cart for subscription-only products
     *
     * @param Quote $subject
     * @param \Closure $proceed
     * @param array ...$args
     * @return \Magento\Quote\Model\Quote\Item|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    // @codingStandardsIgnoreStart
    public function aroundAddProduct(Quote $subject, \Closure $proceed, ...$args)
    {
        if (isset($args[0])) {
            /** @var Product $product */
            $product = $args[0];
            if (isset($args[1])) {
                $buyRequest = $args[1];
            } else {
                $buyRequest = null;
            }

            if ($product->getTypeId() == 'configurable' && $buyRequest && $buyRequest->getData('super_attribute')) {
                $isAddToCartAvailable = $this->isAddToCartAvailableForConfigurable($product, $buyRequest);
            } elseif ($product->getTypeId() == 'bundle' && $buyRequest && $buyRequest->getData('bundle_option')) {
                $isAddToCartAvailable = $this->isAddToCartAvailableForBundle($product, $buyRequest);
            } elseif (in_array($product->getTypeId(), ['simple', 'virtual', 'downloadable'])) {
                $isAddToCartAvailable = $this->isAddToCartAvailableForSimple($product);
            } else {
                $isAddToCartAvailable = true;
            }

            if (!$isAddToCartAvailable) {
                return strval(__('Please specify product option(s).'));
            }
        }
        $result = $proceed(...$args);
        return $result;
    }
    // @codingStandardsIgnoreEnd

    /**
     * Check if add to cart is available for simple product
     *
     * @param Product $product
     * @return bool
     */
    private function isAddToCartAvailableForSimple($product)
    {
        if (!$product->getAwSarpSubscriptionType()) {
            return true;
        }
        return $product->getAwSarpSubscriptionType() != SubscriptionType::SUBSCRIPTION_ONLY;
    }

    /**
     * Check if add to cart is available for configurable product
     *
     * @param Product $product
     * @param DataObject $buyRequest
     * @return bool
     */
    private function isAddToCartAvailableForConfigurable($product, $buyRequest)
    {
        /** @var TypeConfigurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($product->getStoreId(), $product);
        $childProduct = $productTypeInstance->getProductByAttributes($buyRequest->getData('super_attribute'), $product);

        return $this->isAddToCartAvailableForSimple($childProduct);
    }

    /**
     * Check if add to cart is available for bundle product
     *
     * @param Product $product
     * @param DataObject $buyRequest
     * @return bool
     */
    private function isAddToCartAvailableForBundle($product, $buyRequest)
    {
        $bundleOptions = $this->bundleProductOptionRepository->getList($product->getData('sku'));
        $isAddToCartAvailable = true;
        foreach ($buyRequest->getData('bundle_option') as $bundleOptionId => $bundleOptionValue) {
            foreach ($bundleOptions as $option) {
                $optionChecked = false;
                if ($option->getOptionId() == $bundleOptionId) {
                    foreach ($option->getProductLinks() as $productLink) {
                        if ($productLink->getId() == $bundleOptionValue) {
                            $childProduct = $this->productRepository->get($productLink->getSku());
                            $isAddToCartAvailable = $this->isAddToCartAvailableForSimple($childProduct);
                            $optionChecked = true;
                            break;
                        }
                    }
                    if ($optionChecked) {
                        break;
                    }
                }
            }
            if (!$isAddToCartAvailable) {
                return false;
            }
        }

        return true;
    }
}
