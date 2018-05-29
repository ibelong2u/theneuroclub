<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Checkout\ConfigProvider;

use Aheadworks\Sarp\Model\Checkout\ConfigProviderInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product as CatalogProduct;

/**
 * Class Product
 * @package Aheadworks\Sarp\Model\Checkout\ConfigProvider
 */
class Product implements ConfigProviderInterface
{
    /**
     * @var Persistor
     */
    private $cartPersistor;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param Persistor $cartPersistor
     * @param ImageHelper $imageHelper
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Persistor $cartPersistor,
        ImageHelper $imageHelper,
        ProductRepositoryInterface $productRepository
    ) {
        $this->cartPersistor = $cartPersistor;
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return ['imageData' => $this->getImageData()];
    }

    /**
     * Get image data
     *
     * @return array
     */
    private function getImageData()
    {
        $imageData = [];

        if ($this->cartPersistor->getCartId()) {
            $cart = $this->cartPersistor->getSubscriptionCart();
            foreach ($cart->getItems() as $item) {
                /** @var ProductInterface|CatalogProduct $product */
                $product = $this->productRepository->getById($item->getProductId());
                $this->imageHelper->init($product, 'mini_cart_product_thumbnail');
                $imageData[$item->getItemId()] = [
                    'src' => $this->imageHelper->getUrl(),
                    'alt' => $this->imageHelper->getLabel(),
                    'width' => $this->imageHelper->getWidth(),
                    'height' => $this->imageHelper->getHeight(),
                ];
            }
        }

        return $imageData;
    }
}
