<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 24/05/2016
 * Time: 17:01
 */

namespace Magenest\Stripe\Model\Plugin;

use Psr\Log\LoggerInterface;

class Nominal
{
    protected $_logger;

    public function __construct(
        LoggerInterface $loggerInterface
    ) {
        $this->_logger = $loggerInterface;
    }

    public function aroundAddItem(
        \Magento\Quote\Model\Quote $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item $item
    ) {
        $itemCount = $subject->getItemsCount();
        if ($itemCount == 0) {
            $proceed($item);

            return $subject;
        }

        $subscriptionCart = 0;
        foreach ($subject->getAllVisibleItems() as $cartItem) {
            $buyInfo = $cartItem->getBuyRequest();
            $stripeSubscription = $buyInfo->getData('stripe_subscription');
            if (count($stripeSubscription)) {
                $subscriptionCart = 1;
            }
        }

        $itemSubscriptionAdding = 0;
        $buyInfo = $item->getBuyRequest();

        if ($stripeSubscription = $buyInfo->getData('stripe_subscription')) {
            if (count($stripeSubscription)>0) {
                $itemSubscriptionAdding = 1;
            }
        }
        if ($subscriptionCart !== $itemSubscriptionAdding) {
            throw new \Exception(
                __('You cannot add this product to cart')
            );
        }
//        if ($subscriptionCart) {
//            throw new \Exception(
//                __('Item with subscription option can be purchased standalone only.')
//            );
//        } else {
//            if ($itemSubscriptionAdding) {
//                throw new \Exception(
//                    __('Item with subscription option can be purchased standalone only.')
//                );
//            }
//        }

        $proceed($item);
        return $subject;
    }
}
