<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 24/05/2016
 * Time: 02:05
 */

namespace Magenest\Stripe\Observer\Layout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class Add implements ObserverInterface
{
    protected $subscriptionHelper;

    /**
     * @var RequestInterface
     */
    protected $_request;

    protected $stripeConfig;

    public function __construct(
        \Magenest\Stripe\Helper\SubscriptionHelper $subscriptionHelper,
        RequestInterface $request,
        \Magenest\Stripe\Helper\Config $stripeConfig
    ) {
        $this->subscriptionHelper = $subscriptionHelper;
        $this->_request = $request;
        $this->stripeConfig = $stripeConfig;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getId();
        $isSubscription = $this->subscriptionHelper->isSubscriptionProduct($productId);
        // Check and set information according to your need
        if ($isSubscription) {
            $product->setHasOptions($isSubscription);
        }
        $fullActionName = $this->_request->getFullActionName();
        if (($fullActionName == 'checkout_cart_add')
            ||($fullActionName == 'checkout_cart_updateItemOptions')) {
            $maxTotalCycle = $this->stripeConfig->getMaxTotalCycle();
            $dataSubscription = $this->_request->getParam('stripe_subscription');
            $planId = isset($dataSubscription['plan_id'])?$dataSubscription['plan_id']:"";
            $totalCycle = isset($dataSubscription['total_cycles'])?$dataSubscription['total_cycles']:"";
            if ($maxTotalCycle && ($totalCycle > $maxTotalCycle)) {
                $totalCycle = $maxTotalCycle;
            }
            if ($dataSubscription && $planId) {
                $additionalOptions = $this->subscriptionHelper->getSubscriptionAdditionalOption($productId, $planId, $totalCycle);
                $product->addCustomOption('additional_options', $this->subscriptionHelper->encodeProductData($additionalOptions));
            }
        }
    }
}
