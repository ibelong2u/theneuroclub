<?php
namespace Quinoid\Subscription\Observer;
//use Psr\Log\LoggerInterface;

class Bundleitem implements \Magento\Framework\Event\ObserverInterface
{
  /** @var LoggerInterface  */
    protected $helper;
    protected $cartHelper;
    protected $bundleHelper;

 public function __construct(
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Quinoid\Subscription\Helper\Data $helper,
        \Quinoid\Subscription\Helper\BundleCollection $bundleHelper
    ) {
        $this->cartHelper = $cartHelper;
        $this->helper = $helper;
        $this->bundleHelper = $bundleHelper;
    }

public function execute(\Magento\Framework\Event\Observer $observer)
{
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $logger = $objectManager->get("Psr\Log\LoggerInterface");
    $simpleItemsCart = array();
    $bundleItemsCart = array();
    //Check whether the cart has any items
    if ($this->cartHelper->getItemsCount() !== 0) {
          //add your functionality
          $addedItemId = $observer->getRequest()->getParam('product');

          //Check whether the added item is bundle or simple
          if(!($this->helper->isBundle($addedItemId))) {
                  //Added item is Simple product
                  //get all cart items
                  $cartItem = $this->helper->getCartItems();
                  $cartItems = json_decode($cartItem);
                  $logger->info(100,$cartItems);
                  $simpleincr = $bundleincr = $bundleSimple = $simpleSimple = 0;
                  foreach($cartItems as $item) {
                      //check whether each items in cart is bundle or simple
                      if($this->helper->isBundle($item)) {
                        //Item in cart is Bundle and added item is simple
                        $bundleItemsCart[$bundleincr] = $item;
                        $bundleincr++;
                        $bundleSimple =1;
                        $logger->info("bundle". $item);
                      }
                      else {
                        //Item in cart is Simple and added item is also simple
                        $simpleItemsCart[$simpleincr] = $item;
                        $simpleincr++;
                        $simpleSimple=1;
                        $logger->info('simple'. $item);
                      }
                  }
                  // Check which combination is going
                  if($bundleSimple == 1) {
                    //Check whether there is any other bundle combination
                    foreach($bundleItemsCart as $item){
                      $bundledItem   = $this->bundleHelper->getBundledItems($item);
                      $bundledItems = json_decode($bundledItem);
                      if(count($bundledItems) < 3){
                         $logger->info(100,$bundledItems);
                         $bundledItems[count($bundledItems)+1] = $addedItemId;
                         $parentId = $this->bundleHelper->getParentBundle($bundledItems,count($bundledItems));
                         $logger->info("parentId bundle".$parentId);
                         // remove the existing bundled products

                         //Add the new bundled product

                      }
                    }
                  }
                  // Check Simple- Simple combination
                  if($simpleSimple == 1){
                    $simpleItemsCart[count($simpleItemsCart)+1] = $addedItemId;
                    $parentId = $this->bundleHelper->getParentBundle($simpleItemsCart,$simpleincr);
                    //remove the products add the new bundled item
                    $logger->info('incr = '. $simpleincr. " Parent simple = ". $parentId);
                  }
              }

    }



//$counter = $this->_cartHelper('\Magento\Checkout\Helper\Cart');
  //  echo $counter->getItemsCount();
  //  $logger->info('test'.$this->_cartHelper()->getItemsCount());


      // if cart has item{ check the item in the cart is bundle}

  }
}
