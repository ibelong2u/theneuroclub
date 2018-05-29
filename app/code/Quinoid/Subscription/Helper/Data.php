<?php
namespace Quinoid\Subscription\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_productRepository;
    protected $cart;
    protected $jsonHelper;

    public function __construct(
      \Magento\Catalog\Model\ProductRepository $productRepository,
      \Magento\Checkout\Model\Cart $cart,
      \Magento\Framework\Json\Helper\Data $jsonHelper
    )
    {
       $this->_productRepository = $productRepository;
       $this->cart = $cart;
       $this->jsonHelper = $jsonHelper;
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
      $om =   \Magento\Framework\App\ObjectManager::getInstance();
      $logger = $om->get("Psr\Log\LoggerInterface");
      $product = $this->getProductById($productId);
      $productType = $product->getTypeID();
      return $productType == "bundle"? true:false;
    }

    /*  Get all items in the cartHelper   */
    public function getCartItems(){
      $om =   \Magento\Framework\App\ObjectManager::getInstance();
      $cartData = $om->create('\Magento\Checkout\Model\Session')->getQuote()->getAllVisibleItems();
      $logger = $om->get("Psr\Log\LoggerInterface");
      $i=0;
      $idArr = array();
      foreach( $cartData as $item ):
          $product = $item->getProduct();
          $idArr[$i] = $product->getId();
          $i++;
      endforeach;
      $encodedData = $this->jsonHelper->jsonEncode($idArr);
      $logger->info('items', $idArr);
      return $encodedData;
    }
}
?>
