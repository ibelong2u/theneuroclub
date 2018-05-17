<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 28/05/2016
 * Time: 13:33
 */

namespace Magenest\Stripe\Controller\Customer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;

class Viewinvoices extends Action
{
    protected $_customerSession;
    protected $_pageFactory;
    protected $_coreRegistry;
    protected $subscriptionHelper;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magenest\Stripe\Helper\SubscriptionHelper $subscriptionHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_pageFactory = $pageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->subscriptionHelper = $subscriptionHelper;
        parent::__construct($context);
    }

    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    public function execute()
    {
        $subscriptionId = $this->getRequest()->getParam('sub_id');
        $customerId = $this->_customerSession->getCustomerId();
        $subscriptionModel = $this->subscriptionHelper->getSubscription($subscriptionId);
        $dataCusId = $subscriptionModel->getData('customer_id');
        if ($customerId != $dataCusId) {
            return $this->_redirect("stripe/customer/subscription");
        }
        $this->_coreRegistry->register("stripe_subscription_model", $subscriptionModel);
        return $this->_pageFactory->create();
    }
}
