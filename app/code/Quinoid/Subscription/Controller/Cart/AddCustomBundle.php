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
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;

class AddCustomBundle extends \Magento\Framework\App\Action\Action
{
    protected $_helper;

    protected $helperBundle;

    private $cartManagement;

    private $cartPersistor;

    private $itemFactory;
    
    public function __construct(
        Context $context,
        \Quinoid\Subscription\Helper\Data $helper,
        \Quinoid\Subscription\Helper\BundleCollection $helperBundle,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        SubscriptionsCartManagementInterface $cartManagement,
        CartPersistor $cartPersistor,
        SubscriptionsCartItemInterfaceFactory $itemFactory
        
    ) {
        
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_helperBundle = $helperBundle;
        $this->_formKey = $formKey;
        $this->resultRedirect = $result;
        $this->_jsonHelper = $jsonHelper;
        $this->cartManagement = $cartManagement;
        $this->cartPersistor = $cartPersistor;
        $this->itemFactory = $itemFactory;    
    }

    public function execute()
    {   
        $params = $this->getRequest()->getParams();
        if(!empty($params['customitems'])) {
            if (count($params['customitems']) > 0) {
                $bundleItem = $this->_helperBundle->getParentBundle($params['customitems'],count($params['customitems']));
                $bundleItemArr = $this->_jsonHelper->jsonDecode($bundleItem);
                if ($bundleItemArr['found'] != null) {
                    $itemDetails = array();
                    $itemDetails['product'] = $bundleItemArr['bundleid'];
                    $itemDetails['form_key'] = $this->_formKey->getFormKey();
                    $itemDetails['bundle_option'] = $this->_helperBundle->getBundledItemsDetails($bundleItemArr['bundleid']);
                    $itemDetails['qty'] = 1;
                }
                $cart = $this->cartPersistor->getSubscriptionCart();
                /** @var SubscriptionsCartItemInterface $cartItem */
                $cartItem = $this->itemFactory->create();
                $cartItem
                    ->setProductId($itemDetails['product'])
                    ->setBuyRequest($this->getBuyRequestSerialized($itemDetails));
                if (isset($itemDetails['qty'])) {
                    $cartItem->setQty($itemDetails['qty']);
                }
                $cartItem = $this->cartManagement->add($cart, $cartItem);
                $this->cartPersistor->setCartId($cart->getCartId());

                $this->messageManager->addSuccessMessage(
                    __('You added %1 to subscription cart.', $cartItem->getName())
                );
                $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('aw_sarp/cart/index');
                return $resultRedirect;
            }
        }
        else {
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