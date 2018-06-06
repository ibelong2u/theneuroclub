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
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $params = $this->getRequest()->getParams();
        $result ='';
        try {
            $cart = $this->cartPersistor->getSubscriptionCart();
            /*===== Additional code for the customisation of bundling items =====*/
            $cartId = $cart->getCartId();
            if ($cartId != '' && isset($cartId)) {
                $result = $this->getBundledItem($params['product'],$cart->getCartId(),$params['form_key']);
                if(isset($result)){
                    $resultData = $this->jsonHelper->jsonDecode($result);
                    $params   = $resultData['params'];
                    //Existing cart items to be deleted 
                    $deleting = $resultData['deleted'];
                    if(count($deleting)>0){
                        foreach($deleting as $pid => $itemid) { 
                            $this->cartItemRepo->deleteById($cartId,$itemid); 
                        }
                    }
                    
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
        $simpleincr = $bundleincr = $bundleSimple = $simpleSimple = 0;
        try {
            //If cart has any items in the cart
            if($cartId != '' && isset($cartId)){
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
                        // Check Simple- Simple combination
                        if(($bundleSimple == 0) && ($simpleSimple == 1)){
                            $simpleItemsCart[count($simpleItemsCart)+1] = $addedItem;
                            $parentItems = $this->bundleHelper->getParentBundle($simpleItemsCart,count($simpleItemsCart));
                            //remove the products add the new bundled item
                            $parentDetails = $this->jsonHelper->jsonDecode($parentItems);
                            if($parentDetails['bundleid'] != 0) {                                       $paramsArr = $this->setParamsArray($parentDetails['bundleid'],1,$form_key);
                                $result['deleted'] = $removeCartItemsimple;
                                $result['params'] = $paramsArr;
                                $resultData = $this->jsonHelper->jsonEncode($result);
                                return $resultData;
                            }
                        
                        } elseif(($bundleSimple == 1) && ($simpleSimple == 0)) {
                            //Check whether there is any other bundle combination
                            foreach($bundleItemsCart as $key=>$item){
                                $bundledItem   = $this->bundleHelper->getBundledItems($item);
                                $bundledItems =  $this->jsonHelper->jsonDecode($bundledItem);       
                                if(count($bundledItems) < 3){
                                    $bundledItems[count($bundledItems)+1] = $addedItem;
                                    $parentItems = $this->bundleHelper->getParentBundle($bundledItems,count($bundledItems));
                                    $parentDetails = $this->jsonHelper->jsonDecode($parentItems);
                                    if($parentDetails['bundleid'] != 0){ 
                                      //Add the new bundled product
                                        if(!(in_array($parentDetails['bundleid'],$bundleItemsCart))){
                                            $foundProduct = 1;
                                            $paramsArr = $this->setParamsArray($parentDetails['bundleid'],1,$form_key);
                                            $result['deleted'] = $removeCartItems;                  $result['params'] = $paramsArr;
                                            $resultData = $this->jsonHelper->jsonEncode($result);
                                            return $resultData;   
                                        }  else {
                                            $paramsArr = $this->setParamsArray($parentDetails['bundleid'],1,$form_key);
                                            unset($removeCartItems[$parentDetails['bundleid']]);
                                            $result['deleted'] = $removeCartItems;                  $result['params'] = $paramsArr;
                                            $resultData = $this->jsonHelper->jsonEncode($result);
                                            return $resultData;   
                                        }              
                                    
                                    }
                                }
                            }
                        }elseif($bundleSimple == 1 && $simpleSimple == 1) {
                            $foundProduct = 0;
                            if(count($simpleItemsCart) > 0){
                                $simpleItemsCart[count($simpleItemsCart)+1] = $addedItem;
                                $parentItems = $this->bundleHelper->getParentBundle($simpleItemsCart,count($simpleItemsCart));
                                $parentDetails = $this->jsonHelper->jsonDecode($parentItems);                                            
                                if($parentDetails['bundleid'] != 0) {   
                                    $foundProduct = 1;
                                    $paramsArr = $this->setParamsArray($parentDetails['bundleid'],1,$form_key);
                                    $result['deleted'] = $removeCartItemsimple;
                                    $result['params'] = $paramsArr;
                                    $resultData = $this->jsonHelper->jsonEncode($result);
                                    return $resultData;
      
                                }
                            }
                            if(($foundProduct == 0) && (count($bundleItemsCart) > 0)){
                                $parentDetails = $this->setSimpleBundle($bundleItemsCart,$form_key);
                                if($parentDetails['bundleid'] != 0){ 
                                    //Add the new bundled product
                                    if(!(in_array($parentDetails['bundleid'],$bundleItemsCart))){
                                        $foundProduct = 1;
                                        $paramsArr = $this->setParamsArray($parentDetails['bundleid'],1,$form_key);
                                        $result['deleted'] = $removeCartItems;                      $result['params'] = $paramsArr;
                                        $resultData = $this->jsonHelper->jsonEncode($result);
                                        return $resultData;   
                                    }  else {
                                        $paramsArr = $this->setParamsArray($parentDetails['bundleid'],1,$form_key);
                                        $result['deleted'] = $removeCartItems;                      $result['params'] = $paramsArr;
                                        $resultData = $this->jsonHelper->jsonEncode($result);
                                        return $resultData;   
                                    }                         
                                }
                            }
                            return '';
                           
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
