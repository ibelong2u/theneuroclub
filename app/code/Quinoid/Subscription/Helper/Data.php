<?php
namespace Quinoid\Subscription\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_productRepository;

    public function __construct(
      \Magento\Catalog\Model\ProductRepository $productRepository
    )
    {
      $this->_productRepository = $productRepository;
    }
    //Get product details using productid
    public function getProductById($id)
    {
      return $this->_productRepository->getById($id);
    }
    // Function takes as input  product id and returns the type of product.
    //@params $productId
    //@returns boolean
    public function isBundle($productId)
    {
      $product = $this->getProductById($productId);
      $productType = $product->getTypeID();
      return $productType == "bundle"? true:false;
    }


}
?>
