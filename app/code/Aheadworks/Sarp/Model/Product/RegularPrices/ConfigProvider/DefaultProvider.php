<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Product\RegularPrices\ConfigProvider;

use Aheadworks\Sarp\Model\Product\RegularPrices\ConfigProviderInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * Class DefaultProvider
 * @package Aheadworks\Sarp\Model\Product\RegularPrices\ConfigProvider
 */
class DefaultProvider implements ConfigProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOptionsConfig($product)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceWithoutOptions($product)
    {
        return $product->getPriceInfo()
            ->getPrice(FinalPrice::PRICE_CODE)
            ->getAmount()
            ->getValue();
    }
}
