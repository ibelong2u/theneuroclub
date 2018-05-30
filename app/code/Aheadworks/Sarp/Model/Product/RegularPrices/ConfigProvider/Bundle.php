<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Product\RegularPrices\ConfigProvider;

use Aheadworks\Sarp\Model\Product\RegularPrices\ConfigProviderInterface;
use Aheadworks\Sarp\Model\Product\SubscribeAbilityChecker;
use Magento\Bundle\Api\ProductOptionRepositoryInterface as BundleProductOptionRepository;
use Magento\Bundle\Model\Product\Price as BundlePrice;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as BundleSelectionCollection;
use Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory as BundleSelectionCollectionFactory;
use Magento\Bundle\Model\Selection as BundleSelection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Bundle
 * @package Aheadworks\Sarp\Model\Product\RegularPrices\ConfigProvider
 */
class Bundle implements ConfigProviderInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var BundleProductOptionRepository
     */
    private $bundleProductOptionRepository;

    /**
     * @var BundleSelectionCollectionFactory
     */
    private $bundleSelectionCollectionFactory;

    /**
     * @var SubscribeAbilityChecker
     */
    private $subscribeAbilityChecker;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param BundleProductOptionRepository $bundleProductOptionRepository
     * @param BundleSelectionCollectionFactory $bundleSelectionCollectionFactory
     * @param SubscribeAbilityChecker $subscribeAbilityChecker
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        BundleProductOptionRepository $bundleProductOptionRepository,
        BundleSelectionCollectionFactory $bundleSelectionCollectionFactory,
        SubscribeAbilityChecker $subscribeAbilityChecker,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->productRepository = $productRepository;
        $this->bundleProductOptionRepository = $bundleProductOptionRepository;
        $this->bundleSelectionCollectionFactory = $bundleSelectionCollectionFactory;
        $this->subscribeAbilityChecker = $subscribeAbilityChecker;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsConfig($product)
    {
        $options = [];
        $bundleOptions = $this->bundleProductOptionRepository->getList($product->getSku());
        foreach ($bundleOptions as $option) {
            $optionId = $option->getOptionId();
            $optionType = $option->getType();

            $inputName = 'bundle_option[' . $optionId . ']';
            if ($optionType == 'multi') {
                $inputName .= '[]';
            }
            $inputQtyName = 'bundle_option_qty[' . $optionId . ']';
            if ($optionType != 'checkbox') {
                $options[$inputName] = [];
            }

            /** @var BundleSelectionCollection $selectionCollection */
            $selectionCollection = $this->bundleSelectionCollectionFactory->create();
            $selectionCollection->setOptionIdsFilter([$option->getOptionId()]);
            /** @var BundleSelection $selection */
            foreach ($selectionCollection as $selection) {
                $selProductId = $selection->getProductId();
                $selectionId = $selection->getSelectionId();

                $selectionProduct = $this->productRepository->getById($selProductId);
                $selectionConfig = [
                    'optionId' => $optionId,
                    'value' => $this->priceCurrency->convertAndRound(
                        $selectionProduct->getAwSarpRegularPrice()
                    ),
                    'defaultQty' => $selection->getSelectionQty(),
                    'inputQtyName' => $inputQtyName,
                    'isSubscribeAvailable' => $this->subscribeAbilityChecker
                        ->isSubscribeAvailable($selectionProduct),
                    'isAddToCartAvailable' => $this->subscribeAbilityChecker
                        ->isAddToCartAvailable($selectionProduct)
                ];
                if ($optionType == 'checkbox') {
                    $options[$inputName . '[' . $selProductId . ']'][$selectionId] = $selectionConfig;
                } else {
                    $options[$inputName][$selectionId] = $selectionConfig;
                }
            }
        }
        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceWithoutOptions($product)
    {
        if ($product->getPriceType() == BundlePrice::PRICE_TYPE_FIXED) {
            return 0;
        }
        return $product
            ->getPriceInfo()
            ->getPrice(FinalPrice::PRICE_CODE)
            ->getPriceWithoutOption()
            ->getValue();
    }
}
