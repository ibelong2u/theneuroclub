<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 27/05/2016
 * Time: 10:07
 */

namespace Magenest\Stripe\Block\Adminhtml\Subscription\View\Tabs;

use Magenest\Stripe\Model\SubscriptionInvoice;
use Magento\Framework\ObjectManagerInterface;
use Magenest\Stripe\Helper\Data as DataHelper;

class Invoice extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_objectManager;

    protected $_helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        ObjectManagerInterface $interface,
        DataHelper $dataHelper,
        array $data
    ) {
        $this->_objectManager = $interface;
        $this->_helper = $dataHelper;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    public function getSubscription()
    {
        return $this->_coreRegistry->registry('stripe_subscription_model');
    }

    public function getSubsDetail()
    {
        $sub = $this->getSubscription();
        return $sub->getData();
    }

    public function getSubscriptionItem()
    {
        /** @var \Magenest\Stripe\Model\ResourceModel\SubscriptionItem\Collection $subscriptionItemCollection */
        $subscription = $this->getSubscription();
        $subscriptionId = $subscription->getData('subscription_id');
        $subscriptionItemCollection = $this->_objectManager->get('Magenest\Stripe\Model\ResourceModel\SubscriptionItem\Collection');
        $subscriptionItemCollection->addFieldToFilter("subscription_id", ['eq'=>$subscriptionId]);
        return $subscriptionItemCollection;
    }

    public function getSubscriptionPlanData($subscriptionItem)
    {
        return json_decode($subscriptionItem->getData('plan'), true);
    }

    public function getOrder()
    {
        $subscription = $this->getSubscription();
        $orderId = $subscription->getOrderId();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_objectManager->get('Magento\Sales\Model\Order');
        $order->loadByIncrementId($orderId);

        return $order;
    }


    public function getTabLabel()
    {
        return __('Invoices');
    }

    public function getTabTitle()
    {
        return __('Invoices');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function getCancelUrl($subscriptionId)
    {
        return $this->getUrl(
            'stripe/subscription/cancel',
            ['sub_id' => $subscriptionId]
        );
    }

    public function getInvoices()
    {
        $subscription = $this->getSubscription();
        $subscriptionId = $subscription->getData('subscription_id');
        /** @var \Magenest\Stripe\Model\ResourceModel\SubscriptionInvoice\Collection $invoiceCollection */
        $invoiceCollection = $this->_objectManager->create('\Magenest\Stripe\Model\ResourceModel\SubscriptionInvoice\Collection');
        $invoiceCollection->addFieldToFilter("subscription", $subscriptionId);
        $invoiceCollection->setOrder("date", "DESC");
        $invoicesData = $invoiceCollection->getData();
        return $invoicesData;
    }

    public function getInvoiceStatus($status)
    {
        if ($status == SubscriptionInvoice::STATUS_NEW) {
            return __("New Invoice");
        }
        if ($status == SubscriptionInvoice::STATUS_CREATED_ORDER) {
            return __("Order Created");
        }
        return $status;
    }

    public function getCreateOrderLink($invoiceId)
    {
        return $this->getUrl(
            'stripe/subscription/createOrder',
            ['invoice_id' => $invoiceId]
        );
    }

    public function getViewOrderUrl($orderId)
    {
        return $this->getUrl(
            'sales/order/view',
            ['order_id' => $orderId]
        );
    }
}
