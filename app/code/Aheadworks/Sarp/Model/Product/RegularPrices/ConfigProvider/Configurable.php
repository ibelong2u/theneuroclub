<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Product\RegularPrices\ConfigProvider;

use Aheadworks\Sarp\Model\Product\RegularPrices\ConfigProviderInterface;
use Aheadworks\Sarp\Model\Product\SubscribeAbilityChecker;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\ConfigurableProduct\Api\LinkManagementInterface as ConfigurableLinkManagement;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Configurable
 * @package Aheadworks\Sarp\Model\Product\RegularPrices\ConfigProvider
 */
class Configurable implements ConfigProviderInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ConfigurableLinkManagement
     */
    private $configurableLinkManagement;

    /**
     * @var TypeConfigurable
     */
    private $configurableTypeInstance;

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
     * @param ConfigurableLinkManagement $configurableLinkManagement
     * @param TypeConfigurable $configurableTypeInstance
     * @param SubscribeAbilityChecker $subscribeAbilityChecker
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ConfigurableLinkManagement $configurableLinkManagement,
        TypeConfigurable $configurableTypeInstance,
        SubscribeAbilityChecker $subscribeAbilityChecker,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->productRepository = $productRepository;
        $this->configurableLinkManagement = $configurableLinkManagement;
        $this->configurableTypeInstance = $configurableTypeInstance;
        $this->subscribeAbilityChecker = $subscribeAbilityChecker;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsConfig($product)
    {
        $options = [];
        $regularPrices = [];
        $addToCartAvailability = [];
        $products = [];

        $configurableAttributes = $this->configurableTypeInstance->getConfigurableAttributes($product);
        $childProducts = $this->configurableLinkManagement->getChildren($product->getSku());
        foreach ($childProducts as $childProduct) {
            $childProduct = $this->productRepository->get($childProduct->getSku());
            if ($this->subscribeAbilityChecker->isSubscribeAvailable($childProduct)) {
                $childProductId = $childProduct->getId();
                $regularPrices[$childProductId] = $this->priceCurrency->convertAndRound(
                    $childProduct->getAwSarpRegularPrice()
                );
                $addToCartAvailability[$childProductId] = $this->subscribeAbilityChecker
                    ->isAddToCartAvailable($childProduct);

                foreach ($configurableAttributes as $attribute) {
                    $attributeCode = $attribute->getProductAttribute()->getAttributeCode();
                    $inputName = 'super_attribute[' . $attribute->getAttributeId() . ']';
                    if (!isset($products[$inputName])) {
                        $products[$inputName] = [];
                    }

                    if ($childProduct->hasData($attributeCode)) {
                        $products[$inputName][$childProduct->getData($attributeCode)] = $childProductId;
                    }
                }
            }
        }
        $options['regularPrices'] = $regularPrices;
        $options['addToCartAvailability'] = $addToCartAvailability;
        $options['products'] = $products;
        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceWithoutOptions($product)
    {
        $value = $product->getPriceInfo()
            ->getPrice(FinalPrice::PRICE_CODE)
            ->getAmount()
            ->getValue();
        // Workaround of Magento behavior when minimal price is converted to current currency twice
        return $this->priceCurrency->convertAndRound($value);
    }
}
