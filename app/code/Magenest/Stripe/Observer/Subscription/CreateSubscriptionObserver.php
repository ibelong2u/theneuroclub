<?php
/**
 * Created by PhpStorm.
 * User: hiennq
 * Date: 09/01/2018
 * Time: 10:03
 */

namespace Magenest\Stripe\Observer\Subscription;

use Magento\Framework\Event\ObserverInterface;

class CreateSubscriptionObserver implements ObserverInterface
{
    protected $subscriptionHelper;
    protected $subscriptionModel;

    public function __construct(
        \Magenest\Stripe\Helper\SubscriptionHelper $subscriptionHelper,
        \Magenest\Stripe\Model\Subscription $subscriptionModel
    ) {
    
        $this->subscriptionHelper = $subscriptionHelper;
        $this->subscriptionModel = $subscriptionModel;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $payment = $order->getPayment();
        $subscriptionCheck = $payment->getAdditionalInformation("do_subscription_action");
        if ($subscriptionCheck) {
            $dbSource = $payment->getAdditionalInformation('db_source');
            $paymentToken = $payment->getAdditionalInformation('payment_token');
            $this->subscriptionModel->createSubscription($payment, $paymentToken, $dbSource);
        }
    }
}
