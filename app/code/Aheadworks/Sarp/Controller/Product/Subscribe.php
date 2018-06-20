<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Product;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterfaceFactory;
use Aheadworks\Sarp\Api\SubscriptionsCartManagementInterface;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart\ItemRepository;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor as CartPersistor;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
//use Quinoid\Subscription\Helper\Data;

/**
 * Class Subscribe
 * @package Aheadworks\Sarp\Controller\Product
 */
class Subscribe extends Action
{
    /**
     * @var SubscriptionsCartManagementInterface
     */
    private $cartManagement;

    /**
     * @var CartPersistor
     */
    private $cartPersistor;

    /**
     * @var SubscriptionsCartItemInterfaceFactory
     */
    private $itemFactory;

    protected $helper;

    protected $bundleHelper;

    protected $jsonHelper;

    protected $cartItemRepo;

    /**
     * @param Context $context
     * @param SubscriptionsCartManagementInterface $cartManagement
     * @param CartPersistor $cartPersistor
     * @param SubscriptionsCartItemInterfaceFactory $itemFactory
     */
    public function __construct(
        Context $context,
        SubscriptionsCartManagementInterface $cartManagement,
        CartPersistor $cartPersistor,
        SubscriptionsCartItemInterfaceFactory $itemFactory,
        ItemRepository $cartItemRepo,
        \Quinoid\Subscription\Helper\Data $helper,
        \Quinoid\Subscription\Helper\BundleCollection $bundleHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct($context);
        $this->cartManagement = $cartManagement;
        $this->cartPersistor  = $cartPersistor;
        $this->itemFactory    = $itemFactory;
        $this->helper         = $helper;
        $this->bundleHelper   = $bundleHelper;
        $this->jsonHelper     = $jsonHelper;
        $this->cartItemRepo   =  $cartItemRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var Json $resultJson */
       // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$logger = $objectManager->get("Psr\Log\LoggerInterface");

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $params = $this->getRequest()->getParams();
        $result ='';
        $deleting = array();
        try {
            $cart = $this->cartPersistor->getSubscriptionCart();
            /*===== Additional code for the customisation of bundling items =====*/
            $cartId = $cart->getCartId();
            if ($cartId != '' && isset($cartId)) {
                $result = $this->getBundledItem($params['product'],$cart->getCartId(),$params['form_key']);
                if(isset($result)){
                    $resultData = $this->jsonHelper->jsonDecode($result);            
                    //Existing cart items to be deleted 
                    $deleting = $resultData['deleted'];
                    
                    $params   = $resultData['params'];
                }
            }
            /*==== End of Additional code ===*/
            /** @var SubscriptionsCartItemInterface $cartItem */
            $cartItem = $this->itemFactory->create();
            
            $cartItem
                ->setProductId($params['product'])
                ->setBuyRequest($this->getBuyRequestSerialized($params));
            if (isset($params['qty'])) {
                $cartItem->setQty($params['qty']);
            }
            $cartItem = $this->cartManagement->add($cart, $cartItem);
            
            $this->cartPersistor->setCartId($cart->getCartId());

            if(count($deleting)>0){
                foreach($deleting as $pid => $itemid) { 
                   $this->cartItemRepo->deleteById($cartId,$itemid); 
                }
            }

            $this->messageManager->addSuccessMessage(
                __('You added %1 to subscription cart.', $cartItem->getName())
            );
            return $resultJson->setData(
                ['redirectUrl' => $this->_url->getUrl('aw_sarp/cart/index')]
            );
        } catch (LocalizedException $e) {
            $messages = array_unique(explode('\n', $e->getMessage()));
            foreach ($messages as $message) {
                $this->messageManager->addErrorMessage($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to subscription cart right now.')
            );
        }

        return $resultJson->setData([]);
    }

    /**
     * Get buy request serialized
     *
     * @param array $params
     * @return string
     */
    private function getBuyRequestSerialized($params)
    {
        if (isset($params['form_key'])) {
            unset($params['form_key']);
        }
        return serialize($params);
    }
    
    public function getBundledItem($addedItem,$cartId,$form_key)
    {
        $simpleItemsCart = array();
        $bundleItemsCart = array();
        $bundleOption = array();
        $paramsArr = array();
        $removeCartItems = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $objectManager->get("Psr\Log\LoggerInterface");

        $simpleincr = $bundleincr = $bundleSimple = $simpleSimple = 0;
        try {
            //If cart has any items in the cart
            if($cartId != '' && isset($cartId)){
                $deleteArr = array();
                 //get all cart items
                $cartItem  = $this->bundleHelper->getSubscribeCartItems($cartId);
                $cartItems = $this->jsonHelper->jsonDecode($cartItem);

                if($cartItems['count'] != 0){
                    //Check whether the added item is bundle or simple
                    if(!($this->helper->isBundle($addedItem))) {
                        //Added item is Simple product
  
                        foreach($cartItems as $item){
                            if($item['pid'] != 0){
                               //check whether each items in cart is bundle or simple
                                if($item['ptype'] == "simple"){
                                    $simpleItemsCart[$simpleincr]  = $item['pid'];
                                    $removeCartItemsimple[$item['pid']] = $item['itemid'];
                                    $simpleincr++;
                                    $simpleSimple=1;
                                } else{
                                    // Simple - Bundle combination
                                    //Item in cart is Bundle and added item is simple
                                    $bundleItemsCart[$bundleincr] = $item['pid'];
                                    $removeCartItems[$item['pid']] = $item['itemid'];
                                    $bundleincr++;
                                    $bundleSimple =1;                                    
                                } 
                            }                           
                        }

                    //    $logger->info(" after for loop ",$bundleItemsCart);


                        // Check Simple- Simple combination
                        if(($bundleSimple == 0) && ($simpleSimple == 1)){
                            $logger->info(" count simple = ". count($simpleItemsCart));
                         
                            if(count($simpleItemsCart) > 1){
                              //  $logger->info(" simple cart items= ", $simpleItemsCart); 
                               // $maxBundleItem  = $this->getMaxBundle($simpleItemsCart);
                                for($i=0;$i<count($simpleItemsCart);$i++){
                               //     $logger->info(" for loop ". $addedItem . " " . $simpleItemsCart[$i]);
                                    $tempArr = array($simpleItemsCart[$i],$addedItem);
                                //    $logger->info(" for loop ". $addedItem . " " . $simpleItemsCart[$i]);
                                    $parentItems = $this->bundleHelper->getParentBundle($tempArr,count($tempArr)); 
                                 //   $logger->info(" for loop ".$parentItems);
                                    $parentDetails = $this->jsonHelper->jsonDecode($parentItems);
                                    if($parentDetails['bundleid'] != 0){
                                         //Rewrite the remove cart items
                                        $deleteArr[$simpleItemsCart[$i]] = $removeCartItemsimple[$simpleItemsCart[$i]]; 
                                   //     $logger->info(" removeCartItemsimple ",$deleteArr);
                                        $removeCartItemsimple  =  $deleteArr;
                                    //    $logger->info(" removeCartItemsimple ",$removeCartItemsimple);
                                        break;
                                    }
                                } 
                               
                            } else { 
                                $simpleItemsCart[count($simpleItemsCart)+1] = $addedItem;
                                $parentItems = $this->bundleHelper->getParentBundle($simpleItemsCart,count($simpleItemsCart));
                                $parentDetails = $this->jsonHelper->jsonDecode($parentItems);
                            }
                            //remove the products add the new bundled item
                          //  $logger->info(" Bundle Id= ".$parentDetails['bundleid']);
                            if($parentDetails['bundleid'] != 0) {               
                                $paramsArr = $this->setParamsArray($parentDetails['bundleid'],1,$form_key);
                                $result['deleted'] = $removeCartItemsimple;
                                $result['params'] = $paramsArr;
                                $resultData = $this->jsonHelper->jsonEncode($result);
                             //   $logger->info(" Result Data= ".$resultData);
                                return $resultData;
                            } 
                        
                        } elseif(($bundleSimple == 1) && ($simpleSimple == 0)) {
                            //Check whether there is any other bundle combination
                          //  $logger->info(" bundle=1,simple=0 bundled items if ",$bundleItemsCart);
                            foreach($bundleItemsCart as $key=>$item){
                                $bundledItem   = $this->bundleHelper->getBundledItems($item);
                                $bundledItems =  $this->jsonHelper->jsonDecode($bundledItem); 
                             //   $logger->info(" foreach  count = ".count($bundledItems). $item);

                                if(count($bundledItems) < 3){
                                    $bundledItems[count($bundledItems)+1] = $addedItem;
                                 //   $logger->info(" bundle=1,simple=0 bundled items if ". $item,$bundledItems);
                                    $parentItems = $this->bundleHelper->getParentBundle($bundledItems,count($bundledItems));
                                    $parentDetails = $this->jsonHelper->jsonDecode($parentItems);
                                    if($parentDetails['bundleid'] != 0){ 
                                      //Add the new bundled product
                                        if(!(in_array($parentDetails['bundleid'],$bundleItemsCart))){
                                            $foundProduct = 1;
                                            $paramsArr = $this->setParamsArray($parentDetails['bundleid'],1,$form_key);
                                            $deleteArr[$item] = $removeCartItems[$item]; 
                                            $removeCartItems  =  $deleteArr;
                                            $result['deleted'] = $removeCartItems;      
                                            $result['params'] = $paramsArr;
                                            $resultData = $this->jsonHelper->jsonEncode($result);
                                            return $resultData;   
                                        }  else {
                                            $paramsArr = $this->setParamsArray($parentDetails['bundleid'],1,$form_key);
                                            $logger->info("bundle=1,simple=0 else bundle items cart ".$parentDetails['bundleid'],$bundleItemsCart);

                                            $deleteArr[$item] = $removeCartItems[$item]; 
                                            $logger->info(" removeCartItemsimple ",$deleteArr);
                                            $removeCartItems  =  $deleteArr;
                                            
                                            //unset($removeCartItems[$parentDetails['bundleid']]);
                                            $result['deleted'] = $removeCartItems;        
                                            $result['params'] = $paramsArr;
                                            $resultData = $this->jsonHelper->jsonEncode($result);
                                            return $resultData;   
                                        }              
                                    
                                    }
                                }
                            }
                        }elseif($bundleSimple == 1 && $simpleSimple == 1) {
                            $foundProduct = 0;
                          //  $logger->info(" bundle=1,simple=1 if ",$removeCartItems);
                            $logger->info(" bundle=1,simple=1 simpleItemsCart ".count($simpleItemsCart),$simpleItemsCart);
                            if(count($simpleItemsCart) > 0){
                                $simpleItemsCart[count($simpleItemsCart)+1] = $addedItem;
                               
                                $parentItems = $this->bundleHelper->getParentBundle($simpleItemsCart,count($simpleItemsCart));
                                $parentDetails = $this->jsonHelper->jsonDecode($parentItems);                    //$logger->info("inside if simpleItemsCart ".count($simpleItemsCart), $parentDetails);                       
                                if($parentDetails['bundleid'] != 0) { 
                              //      $logger->info("inside if simpleItemsCart ",$removeCartItemsimple);   
                                    $foundProduct = 1;
                                    $paramsArr = $this->setParamsArray($parentDetails['bundleid'],1,$form_key);
                                    $result['deleted'] = $removeCartItemsimple;
                                    $result['params'] = $paramsArr;
                                 //   $logger->info("found = ".$foundProduct); 
                                    $resultData = $this->jsonHelper->jsonEncode($result);
                                    return $resultData;
      
                                }
                            }
                            $logger->info("found = ".$foundProduct); 
                            if(($foundProduct == 0) && (count($bundleItemsCart) > 0)){
                                //$parentDetails = $this->setSimpleBundle($bundleItemsCart,$form_key);
                                foreach($bundleItemsCart as $key=>$item){
                                    $bundledItem   = $this->bundleHelper->getBundledItems($item);
                                    $bundledItems =  $this->jsonHelper->jsonDecode($bundledItem); 
                                    if(count($bundledItems) < 3){
                                        $bundledItems[count($bundledItems)+1] = $addedItem;
                                     //   $logger->info(" bundle=1,simple=0 bundled items if ". $item,$bundledItems);
                                        $parentItems = $this->bundleHelper->getParentBundle($bundledItems,count($bundledItems));
                                        $parentDetails = $this->jsonHelper->jsonDecode($parentItems);
                                        if($parentDetails['bundleid'] != 0){ 
                                          //Add the new bundled product
                                            if(!(in_array($parentDetails['bundleid'],$bundleItemsCart))){
                                                $foundProduct = 1;
                                                $paramsArr = $this->setParamsArray($parentDetails['bundleid'],1,$form_key);
                                                $deleteArr[$item] = $removeCartItems[$item]; 
                                                $removeCartItems  =  $deleteArr;
                                                $result['deleted'] = $removeCartItems;      
                                                $result['params'] = $paramsArr;
                                                $resultData = $this->jsonHelper->jsonEncode($result);
                                                return $resultData;   
                                            }  else {
                                                $paramsArr = $this->setParamsArray($parentDetails['bundleid'],1,$form_key);
                                                $logger->info("bundle=1,simple=0 else bundle items cart ".$parentDetails['bundleid'],$bundleItemsCart);
    
                                                $deleteArr[$item] = $removeCartItems[$item]; 
                                                $logger->info(" removeCartItemsimple ",$deleteArr);
                                                $removeCartItems  =  $deleteArr;
                                                
                                                //unset($removeCartItems[$parentDetails['bundleid']]);
                                                $result['deleted'] = $removeCartItems;        
                                                $result['params'] = $paramsArr;
                                                $resultData = $this->jsonHelper->jsonEncode($result);
                                                return $resultData;   
                                            }              
                                        
                                        }
                                    }
                                    
                                }

                            }
                          //  return '';
                           
                        }else{
                            return '';
                        } 
                    }
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to subscription cart right now.')
            );
        }
    }

    /*function for setting the params Array*/
    public function setParamsArray($bundleId,$qty,$form_key){

        $bundleOption = $this->bundleHelper->getBundledItemsDetails($bundleId);    
        $paramsArr['product_id'] = $bundleId;
        $paramsArr['product']    = $bundleId;  
        $paramsArr['selected_configurable_option'] = ''; 
        $paramsArr['related_product'] = ''; 
        $paramsArr['form_key'] = $form_key;          
        $paramsArr['bundle_option'] = $bundleOption; 
        $paramsArr['qty'] = $qty;    
        return $paramsArr;        
    }

    /* function for find the simple - bundle combination */
    public function setSimpleBundle($bundleItemsCart,$form_key){
        foreach($bundleItemsCart as $key=>$item){
            $bundledItem   = $this->bundleHelper->getBundledItems($item);
            $bundledItems =  $this->jsonHelper->jsonDecode($bundledItem);         
            if(count($bundledItems) < 3){             
                $bundledItems[count($bundledItems)+1] = $addedItem;                
                $parentItems = $this->bundleHelper->getParentBundle($bundledItems,count($bundledItems));               
                $parentDetails = $this->jsonHelper->jsonDecode($parentItems);              
                return $parentDetails; 
            }
        }
    }
    
}
