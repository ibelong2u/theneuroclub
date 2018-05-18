<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 29/05/2016
 * Time: 02:04
 */

namespace Magenest\Stripe\Block\Customer\Subscription;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\ObjectManagerInterface;
use Magenest\Stripe\Model\SubscriptionFactory;
use Magenest\Stripe\Helper\Data as DataHelper;

class ViewInvoices extends \Magento\Framework\View\Element\Template
{
    protected $stripeHelper;

    protected $_coreRegistry;

    protected $customerSession;

    public function __construct(
        Context $context,
        \Magenest\Stripe\Helper\Data $stripeHelper,
        \Magento\Customer\Model\Session $customerSession,
        $data = []
    ) {
        $this->stripeHelper = $stripeHelper;
        $this->_coreRegistry = $context->getRegistry();
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
    }

    public function getInvoices()
    {
        return [];
        $subscriptionModel = $this->_coreRegistry->registry("stripe_subscription_model");
        $customerID = $this->stripeHelper->getStripeCustomerId();
        $subscriptionId = $subscriptionModel->getData('subscription_id');
        $response = $this->stripeHelper->getAllInvoices($customerID, $subscriptionId);
        $invoices = isset($response['data'])?$response['data']:[];
        return $invoices;
    }
}
