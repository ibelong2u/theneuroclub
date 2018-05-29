<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 02/06/2016
 * Time: 10:57
 */

namespace Magenest\Stripe\Controller\Adminhtml\Subscription;

use Magenest\Stripe\Controller\Adminhtml\Subscription;
use Magenest\Stripe\Model\SubscriptionInvoice;
use Magento\Backend\App\Action;
use Magenest\Stripe\Helper\Data as DataHelper;
use Magento\Framework\Controller\ResultFactory;
use Magenest\Stripe\Model\SubscriptionFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class CreateOrder extends Subscription
{
    protected $stripehelper;

    protected $_subsFactory;

    protected $subscriptionInvoiceFactory;

    public function __construct(
        Action\Context $context,
        DataHelper $dataHelper,
        SubscriptionFactory $subscriptionFactory,
        PageFactory $pageFactory,
        LoggerInterface $loggerInterface,
        Registry $registry,
        \Magenest\Stripe\Model\SubscriptionInvoiceFactory $subscriptionInvoiceFactory
    ) {
        $this->stripehelper = $dataHelper;
        $this->_subsFactory = $subscriptionFactory;
        parent::__construct($context, $pageFactory, $subscriptionFactory, $loggerInterface, $registry);
        $this->subscriptionInvoiceFactory = $subscriptionInvoiceFactory;
    }

    public function execute()
    {
        /**
         * @var \Magento\Customer\Model\Session $customerSession
         * @var \Magenest\Stripe\Helper\SubscriptionHelper $subscriptionHelper
         */
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $subscriptionInvoice = $this->subscriptionInvoiceFactory->create()->load($invoiceId);
        $subscriptionId = $subscriptionInvoice->getData('subscription_id');
        $subscription = $this->_subscriptionFactory->create()->load($subscriptionId);
        $order = $subscription->generateOrder();
        if ($order) {
            $order->save();
            $this->messageManager->addSuccessMessage("Create order success, OrderID: ".$order->getIncrementId());
            $orderId = $order->getId();
            $subscriptionInvoice->setData("order_id", $orderId);
            $subscriptionInvoice->setData("status", SubscriptionInvoice::STATUS_CREATED_ORDER);
            $subscriptionInvoice->save();
        }
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('stripe/subscription/view', ['id'=>$subscriptionId]);
    }
}
