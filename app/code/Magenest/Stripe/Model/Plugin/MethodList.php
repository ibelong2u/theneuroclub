<?php

namespace Magenest\Stripe\Model\Plugin;

class MethodList
{
    protected $subscriptionHelper;

    public function __construct(
        \Magenest\Stripe\Helper\SubscriptionHelper $subscriptionHelper
    )
    {
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function aroundGetAvailableMethods(
        $subject,
        callable $proceed,
        \Magento\Quote\Api\Data\CartInterface $quote
    )
    {
        $items = $quote->getItems();
        $result = $proceed($quote);
        $isSubscription = $this->isSubscriptionOrder($items);
        $listAvailableMethod = [
            "magenest_stripe",
            "magenest_stripe_iframe",
            "magenest_stripe_applepay",
            "magenest_stripe_giropay",
            "magenest_stripe_alipay"
        ];
        foreach ($result as $k => $value){
            $paymentCode = $value->getCode();
            if(in_array($paymentCode, $listAvailableMethod)) {
                if ($isSubscription) {
                    if($paymentCode == "magenest_stripe"){
                    }else{
                        unset($result[$k]);
                    }
                }
            }
        }
        return $result;
    }

    public function isSubscriptionOrder($orderItems)
    {
        foreach ($orderItems as $item) {
            $infoRequest = $item->getOptionByCode('info_buyRequest');
            if($infoRequest){
                $infoRequestValue = $this->subscriptionHelper->decodeProductData($infoRequest->getValue());
                $stripeSubscription = isset($infoRequestValue['stripe_subscription'])?$infoRequestValue['stripe_subscription']:[];
                if (isset($stripeSubscription['plan_id']) && $stripeSubscription['plan_id']) {
                    return true;
                }
            }
        }

        return false;
    }
}
