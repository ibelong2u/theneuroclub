<?php
namespace Quinoid\CatalogWidget\Helper\Product;

use Magento\Catalog\Model\Product as ModelProduct;

class Description extends \Magento\Framework\App\Helper\AbstractHelper {
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
    }

    /**
     *
     * Load the product and return its description
     *
     * @param ModelProduct $product
     */
    public function getProductDescription($product)
    {
        $reloadedProduct = $this->productFactory->create()->load($product->getId());

        return $reloadedProduct->getData('description');
    }
}
