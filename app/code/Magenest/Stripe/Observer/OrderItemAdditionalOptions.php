<?php
/**
 * Created by PhpStorm.
 * User: hiennq
 * Date: 09/01/2018
 * Time: 10:03
 */

namespace Magenest\Stripe\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderItemAdditionalOptions implements ObserverInterface
{
    protected $subscriptionHelper;
    protected $stripeHelper;
    protected $customerSession;

    public function __construct(
        \Magenest\Stripe\Helper\SubscriptionHelper $subscriptionHelper,
        \Magenest\Stripe\Helper\Data $stripeHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
    
        $this->subscriptionHelper = $subscriptionHelper;
        $this->stripeHelper = $stripeHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            /**
             * @var \Magento\Sales\Model\Order\Item $orderItem
             * @var \Magento\Sales\Model\Order $order
             * @var \Magento\Quote\Model\Quote $quote
             * @var \Magento\Quote\Model\Quote\Item $quoteItem
             */
            $quote = $observer->getQuote();
            $order = $observer->getOrder();
            $orderItems = $order->getItems();
            if (($this->subscriptionHelper->isSubscriptionOrder($orderItems))&&($this->customerSession->isLoggedIn())) {
                $stripeCustomerId = $this->stripeHelper->getStripeCustomerId();
                if ($stripeCustomerId) {
                } else {
                    $stripeCustomerId = $this->stripeHelper->createCustomer();
                }
            }
            $quoteItems = [];

            // Map Quote Item with Quote Item Id
            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                $quoteItems[$quoteItem->getId()] = $quoteItem;
            }

            foreach ($order->getAllVisibleItems() as $orderItem) {
                $quoteItemId = $orderItem->getQuoteItemId();
                $quoteItem = $quoteItems[$quoteItemId];
                $additionalOptions = $quoteItem->getOptionByCode('additional_options');
                $stripeOptionsData = $quoteItem->getBuyRequest()->getData('stripe_subscription');
                if ($stripeOptionsData) {
                    $additionalOptionsData = $this->subscriptionHelper->decodeProductData($additionalOptions->getValue());
                    $quoteItem->getProduct()->getData();
                    if (isset($stripeOptionsData['plan_id']) && ($stripeOptionsData['plan_id'])) {
                        // Get Order Item's other options
                        $options = $orderItem->getProductOptions();
                        // Set additional options to Order Item
                        $options['additional_options'] = $additionalOptionsData;
                        $orderItem->setProductOptions($options);
                    }
                }
            }
        } catch (\Exception $e) {
            // catch error if any
        }
    }
}
