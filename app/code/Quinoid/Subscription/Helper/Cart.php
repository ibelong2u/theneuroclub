<?php
namespace Quinoid\Subscription\Helper;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Cart extends \Magento\Framework\App\Helper\AbstractHelper
{
        protected $_response;
        protected $_url;
        /**
         * @var PageFactory
         */
        protected $_resultPageFactory;
        /**
         * @var \Magento\Framework\Data\Form\FormKey
         */
        protected $_formKey;

        /**
         * @param Context $context
         * @param PageFactory $resultPageFactory
         */
         protected $_helperData;

        public function __construct(
            //Context $context,
            \Magento\Framework\Data\Form\FormKey $formKey,
            PageFactory $resultPageFactory,
            \Magento\Framework\App\ResponseInterface $response,
            \Magento\Framework\UrlInterface $url,
            \Quinoid\Subscription\Helper\Data $helperData

        ) {
            //parent::__construct($context);
            $this->_formKey = $formKey;
            $this->_resultPageFactory = $resultPageFactory;
            $this->_response = $response;
            $this->_url = $url;
            $this->_helperData = $helperData;
        }

        /**
         * @param $itemDetails which is an array with product details
         * @return \Magento\Framework\View\Result\Page
         */
        public function addToCart($productId)
        {
            $resultPage = $this->_resultPageFactory->create();
            $itemDetails =   $this->_helperData->getProductDetails(4);
            $itemDetails['form_key'] = $this->_formKey->getFormKey();
            $CustomRedirectionUrl = $this->_url->getUrl('checkout/cart/add/',$itemDetails);
            $this->_response->setRedirect($CustomRedirectionUrl)->sendResponse();
            return $resultPage;
        }

        public function addBundleToCart($productId) {
          $CustomRedirectionUrl = $this->_url->getUrl('subscription/bundle/add',$params = ['product'=>$productId]);
          $this->_response->setRedirect($CustomRedirectionUrl)->sendResponse();

        }

        // Remove from cart using product id
        public function removeFromCart($itemId)
        { echo "Called";
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
          $cart->removeItem($itemId)->save();
        }


}
?>
