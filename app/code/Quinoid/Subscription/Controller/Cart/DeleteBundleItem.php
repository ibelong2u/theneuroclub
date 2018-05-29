<?php
/**
 * Author: Sooraj Sathyan
 */

namespace Quinoid\Subscription\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart as CustomerCart;

class DeleteBundleItem extends \Magento\Framework\App\Action\Action
{
    protected $_helper;
    
    public function __construct(
        Context $context,
        \Quinoid\Subscription\Helper\Cart $helper
    ) {
        
        parent::__construct($context);
        $this->_helper = $helper;
    }

    public function execute()
    {   
        // $allItems = $this->checkoutSession->getQuote()->getAllVisibleItems();
        $params = $this->getRequest()->getParams();
        $items = explode(',', $params['items']);
        print_r($items);
        echo $parent = $params['pid'];
        echo $deleteitem = $this->_helper->removeFromCart($parent);
        // die('test index');
    }
}
?>