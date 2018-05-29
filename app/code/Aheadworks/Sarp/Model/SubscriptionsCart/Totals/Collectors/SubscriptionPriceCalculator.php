<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class SubscriptionPriceCalculator
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors
 */
class SubscriptionPriceCalculator
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get base regular price
     *
     * @param SubscriptionsCartItemInterface $item
     * @param SubscriptionsCartItemInterface[] $childItems
     * @return float
     */
    public function getBaseRegularPrice(SubscriptionsCartItemInterface $item, $childItems = [])
    {
        return $this->getBasePriceByProductType($item, $childItems, 'aw_sarp_regular_price');
    }

    /**
     * Get base trial price
     *
     * @param SubscriptionsCartItemInterface $item
     * @param SubscriptionsCartItemInterface[] $childItems
     * @return float
     */
    public function getBaseTrialPrice(SubscriptionsCartItemInterface $item, $childItems = [])
    {
        return $this->getBasePriceByProductType($item, $childItems, 'aw_sarp_trial_price');
    }

    /**
     * Get base initial fee
     *
     * @param SubscriptionsCartItemInterface $item
     * @param SubscriptionsCartItemInterface[] $childItems
     * @return float
     */
    public function getBaseInitialFee(SubscriptionsCartItemInterface $item, $childItems = [])
    {
        $product = $this->productRepository->getById($item->getProductId());
        $productType = $product->getTypeId();
        return $productType == 'configurable'
            ? $this->getBasePriceForConfigurable($item, $childItems, 'aw_sarp_initial_fee')
            : (float)$product->getAwSarpInitialFee();
    }

    /**
     * Get base subscription price by product type
     *
     * @param SubscriptionsCartItemInterface $item
     * @param SubscriptionsCartItemInterface[] $childItems
     * @param string $priceAttrCode
     * @return float
     */
    private function getBasePriceByProductType($item, $childItems, $priceAttrCode)
    {
        /** @var ProductInterface|Product $product */
        $product = $this->productRepository->getById($item->getProductId());
        $productType = $product->getTypeId();

        if ($productType == 'bundle') {
            return $this->getBasePriceForBundle($item, $childItems, $priceAttrCode);
        } elseif ($productType == 'configurable') {
            return $this->getBasePriceForConfigurable($item, $childItems, $priceAttrCode);
        } else {
            return (float)$product->getDataUsingMethod($priceAttrCode);
        }
    }

    /**
     * Get base subscription price for bundle product
     *
     * @param SubscriptionsCartItemInterface $item
     * @param SubscriptionsCartItemInterface[] $childItems
     * @param string $priceAttrCode
     * @return float
     */
    private function getBasePriceForBundle($item, $childItems, $priceAttrCode)
    {
        $basePrice = 0.0;

        /** @var ProductInterface|Product $parentProduct */
        $parentProduct = $this->productRepository->getById($item->getProductId());
        if ($parentProduct->getDataUsingMethod($priceAttrCode)) {
            $basePrice += (float)$parentProduct->getDataUsingMethod($priceAttrCode);
        }

        foreach ($childItems as $childItem) {
            /** @var ProductInterface|Product $childProduct */
            $childProduct = $this->productRepository->getById($childItem->getProductId());
            $basePrice += (float)$childProduct->getDataUsingMethod($priceAttrCode)
                * (float)$childItem->getQty();
        }

        return $basePrice;
    }

    /**
     * Get base subscription price for configurable product
     *
     * @param SubscriptionsCartItemInterface $item
     * @param SubscriptionsCartItemInterface[] $childItems
     * @param string $priceAttrCode
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getBasePriceForConfigurable($item, $childItems, $priceAttrCode)
    {
        foreach ($childItems as $childItem) {
            /** @var ProductInterface|Product $childProduct */
            $childProduct = $this->productRepository->getById($childItem->getProductId());
            return (float)$childProduct->getDataUsingMethod($priceAttrCode);
        }
        return 0.0;
    }
}
