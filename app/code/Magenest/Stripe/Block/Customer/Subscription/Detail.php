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

class Detail extends \Magento\Framework\View\Element\Template
{
    protected $_subscriptionFactory;

    protected $_objectManager;

    protected $_helper;

    protected $_coreRegistry;

    protected $date;

    public function __construct(
        Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        SubscriptionFactory $subscriptionFactory,
        ObjectManagerInterface $objectManagerInterface,
        DataHelper $dataHelper,
        $data = []
    ) {
        $this->date = $date;
        $this->_subscriptionFactory = $subscriptionFactory;
        $this->_objectManager = $objectManagerInterface;
        $this->_helper = $dataHelper;
        $this->_coreRegistry = $context->getRegistry();
        parent::__construct($context, $data);
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

    public function getViewInvoiceUrl($subscriptionId)
    {
        return $this->getUrl(
            'stripe/customer/viewinvoices',
            ['sub_id' => $subscriptionId]
        );
    }


    public function getCancelUrl($subscriptionId)
    {
        return $this->getUrl(
            'stripe/customer/cancel',
            ['sub_id' => $subscriptionId]
        );
    }

    public function getInvoices()
    {
        $subscription = $this->getSubscription();
        $subscriptionId = $subscription->getData('subscription_id');
        $invoiceCollection = $this->_objectManager->create('\Magenest\Stripe\Model\ResourceModel\SubscriptionInvoice\Collection');
        $invoiceCollection->addFieldToFilter("subscription", $subscriptionId);
        $invoiceCollection->setOrder("date", "DESC");
        $invoicesData = $invoiceCollection->getData();
        return $invoicesData;
    }

    public function getDate()
    {
        $date = $this->date->gmtTimestamp();
        return $date;
    }

    public function getViewOrderUrl($orderId)
    {
        return $this->getUrl(
            'sales/order/view',
            ['order_id' => $orderId]
        );
    }
}
