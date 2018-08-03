<?php
/**
 * Author: Sooraj Sathyan
 */

namespace Quinoid\Subscription\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart as CustomerCart;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterfaceFactory;
use Aheadworks\Sarp\Api\SubscriptionsCartManagementInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor as CartPersistor;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart\ItemRepository;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp\Controller\Product\Subscribe;

class AddCustomBundle extends \Magento\Framework\App\Action\Action
{
    protected $_helper;

    protected $bundleHelper;

    private $cartManagement;

    private $cartPersistor;

    private $itemFactory;

    protected $cartItemRepo;

    protected $subscriber;

    public function __construct(
        Context $context,
        \Quinoid\Subscription\Helper\Data $helper,
        \Quinoid\Subscription\Helper\BundleCollection $bundleHelper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        SubscriptionsCartManagementInterface $cartManagement,
        CartPersistor $cartPersistor,
        ItemRepository $cartItemRepo,
        SubscriptionsCartItemInterfaceFactory $itemFactory,
        Subscribe $subscriber
    ) {

        parent::__construct($context);
        $this->_helper = $helper;
        $this->_bundleHelper = $bundleHelper;
        $this->_formKey = $formKey;
        $this->resultRedirect = $result;
        $this->_jsonHelper = $jsonHelper;
        $this->cartManagement = $cartManagement;
        $this->cartPersistor = $cartPersistor;
        $this->cartItemRepo  = $cartItemRepo;
        $this->itemFactory = $itemFactory;
        $this->subscribe = $subscriber;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $result ='';
        $deleting = array();
        $cart = $this->cartPersistor->getSubscriptionCart();

        $cartItem = $this->itemFactory->create();

        if(!empty($params['customitems']) && count($params['customitems']) > 0) {
          
          /*  if (count($params['customitems']) > 1) {
                // Getting the details of Bundle Product item
                $bundleItem = $this->_bundleHelper->getParentBundle($params['customitems'],count($params['customitems']));
                $bundleItemArr = $this->_jsonHelper->jsonDecode($bundleItem);
                if ($bundleItemArr['found'] != null) {
                    $itemDetails = array();
                    $itemDetails['product'] = $bundleItemArr['bundleid'];
                    $itemDetails['form_key'] = $this->_formKey->getFormKey();
                    $itemDetails['bundle_option'] = $this->_bundleHelper->getBundledItemsDetails($bundleItemArr['bundleid']);
                    $itemDetails['qty'] = 1;
                }
            }
            elseif (count($params['customitems']) == 1) {
                //Getting the detail of Simple Product
                $itemDetails = array();
                $itemDetails['product'] = $params['customitems'][0];
                $itemDetails['form_key'] = $this->_formKey->getFormKey();
                $itemDetails['qty'] = 1;
                $cartId = $cart->getCartId();
                if ($cartId != '' && isset($cartId)) {
                    $result = $this->subscribe->getBundledItem($itemDetails['product'],$cart->getCartId(),$itemDetails['form_key']);
                    if(isset($result)){
                        $resultData = $this->_jsonHelper->jsonDecode($result);
                        //Existing cart items to be deleted
                        $deleting = $resultData['deleted'];

                        $params   = $resultData['params'];
                        // var_dump($itemDetails['form_key']);
                        // var_dump($params);
                        // exit;
                        $itemDetails['product'] = $params['product'];
                        $itemDetails['bundle_option'] = $params["bundle_option"];
                    }
                }
            }

            //Adding the Product Item to Subscription Cart
            $cartItem
                ->setProductId($itemDetails['product'])
                ->setBuyRequest($this->getBuyRequestSerialized($itemDetails));
            if (isset($itemDetails['qty'])) {
                $cartItem->setQty($itemDetails['qty']);
            }
            $cartItem = $this->cartManagement->add($cart, $cartItem);
            $this->cartPersistor->setCartId($cart->getCartId());
          */

          foreach($params['customitems'] as $key=>$val){
            $itemDetails = array('product'=>$val, 'form_key'=>$this->_formKey->getFormKey(), 'qty'=>1);
            //Adding the Product Item to Subscription Cart
            $cartItem
                ->setProductId($itemDetails['product'])
                ->setBuyRequest($this->getBuyRequestSerialized($itemDetails));
            if (isset($itemDetails['qty'])) {
                $cartItem->setQty($itemDetails['qty']);
            }
            $cartItem = $this->cartManagement->add($cart, $cartItem);
            $this->cartPersistor->setCartId($cart->getCartId());
          }
            //to delete simple items if customized bundle item is found
            if(count($deleting)>0){
                foreach($deleting as $pid => $itemid) {
                   $this->cartItemRepo->deleteById($cartId,$itemid);
                }
            }

            $this->messageManager->addSuccessMessage(
                __('You added %1 to subscription cart.', $cartItem->getName())
            );
            //Redirection to Cart Index Page
            $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('aw_sarp/cart/index');
            return $resultRedirect;

        }
        else {
            // Redirection to PDP if no item is selected
            if(!empty($params['urlkey'])) {
                $urlKey = $params['urlkey'];
                $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath($urlKey);
                return $resultRedirect;
            }
        }
    }

    private function getBuyRequestSerialized($params)
    {
        if (isset($params['form_key'])) {
            unset($params['form_key']);
        }
        return serialize($params);
    }


}
?>
