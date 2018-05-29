<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Product;

use Aheadworks\Sarp\Model\Product\RegularPrices\ConfigProviderPool;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class RegularPricesConfigProvider
 * @package Aheadworks\Sarp\Model\Product
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RegularPricesConfigProvider
{
    /**
     * @var ConfigProviderPool
     */
    private $configProviderPool;

    /**
     * @var SubscribeAbilityChecker
     */
    private $subscribeAbilityChecker;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param ConfigProviderPool $configProviderPool
     * @param SubscribeAbilityChecker $subscribeAbilityChecker
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ConfigProviderPool $configProviderPool,
        SubscribeAbilityChecker $subscribeAbilityChecker,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->configProviderPool = $configProviderPool;
        $this->subscribeAbilityChecker = $subscribeAbilityChecker;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get regular prices config for product page
     *
     * @param ProductInterface $product
     * @return array
     */
    public function getConfig($product)
    {
        $typeId = $product->getTypeId();
        $typeSpecificProvider = $this->configProviderPool->get($typeId);
        return [
            'productType' => $typeId,
            'options' => $typeSpecificProvider->getOptionsConfig($product),
            'priceWithoutOptions' => $typeSpecificProvider->getPriceWithoutOptions($product),
            'regularPrice' => $this->priceCurrency->convertAndRound($product->getAwSarpRegularPrice()),
            'isAddToCartAvailable' => $this->subscribeAbilityChecker->isAddToCartAvailable($product)
        ];
    }
}
