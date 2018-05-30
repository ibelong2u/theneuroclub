<?php
namespace Quinoid\Subscription\Controller\Bundle;
use Magento\Framework\App\Action\Context;
class Add extends \Magento\Framework\App\Action\Action
{   protected $helperBundle;
    protected $_helper;
    protected $_formKey;
    protected $_cart;
    public function __construct(
        Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Quinoid\Subscription\Helper\Data $helper,
        \Quinoid\Subscription\Helper\BundleCollection $helperBundle,
        \Magento\Checkout\Model\Cart $cart

        ) {
        parent::__construct($context);
        $this->_formKey = $formKey;
        $this->_helper = $helper;
        $this->_helperBundle = $helperBundle;
        $this->_cart = $cart;
    }

    public function execute()
    {   $params = $this->getRequest()->getParams();
        $productId = $params['product'];
        $itemDetails = $this->_helperBundle->getBundledItemsDetails($productId);
        $itemDetails['form_key'] = $this->_formKey->getFormKey();
        $_product = $this->_helper->getProductById($productId);
        $this->_cart->addProduct($_product,$itemDetails);
        $this->_cart->save();
     }


}
?>
