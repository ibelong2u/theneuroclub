<?php
/**
 * Created by PhpStorm.
 * User: magenest
 * Date: 10/07/2017
 * Time: 16:00
 */

namespace Magenest\Stripe\Helper;

class SubscriptionHelper
{
    protected $_helper;
    protected $productAttributeFactory;
    protected $productMetadataInterface;
    protected $subscriptionFactory;
    protected $subscriptionItemFactory;

    public function __construct(
        Data $stripeDataHelper,
        \Magenest\Stripe\Model\AttributeFactory $productAttributeFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
        \Magenest\Stripe\Model\SubscriptionFactory $subscriptionFactory,
        \Magenest\Stripe\Model\SubscriptionItemFactory $subscriptionItemFactory
    ) {
        $this->_helper = $stripeDataHelper;
        $this->productAttributeFactory = $productAttributeFactory;
        $this->productMetadataInterface = $productMetadataInterface;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionItemFactory = $subscriptionItemFactory;
    }

    public function retrievePlan($planId)
    {
        $url = 'https://api.stripe.com/v1/plans/' . urlencode($planId);
        $response = $this->_helper->sendRequest("", $url, 'post');

        return $response;
    }

    public function calTrialPeriodDay($planId)
    {
        try {
            $day = 0;
            $planData = $this->retrievePlan($planId);
            if (isset($planData['object']) && ($planData['object'] == 'plan')) {
                $interval = $planData['interval'];
                $intervalCount = $planData['interval_count'];
                $day = $intervalCount;
                switch ($interval) {
                    case 'day':
                        break;
                    case 'week':
                        $day = $intervalCount * 7;
                        break;
                    case 'month':
                        $day = $intervalCount * 30;
                        break;
                    case 'year':
                        $day = $intervalCount * 365;
                        break;
                }
            }

            return $day;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function createProduct($name)
    {
        $url = 'https://api.stripe.com/v1/products';
        $params = [
            'name' => $name,
            'type' => "service"
        ];
        $response = $this->_helper->sendRequest($params, $url, 'post');

        return $response;
    }

    public function createPlan($currency, $interval, $intervalNum, $productId, $amount, $nickName = "", $trialDay = false)
    {
        if (!$this->_helper->isZeroDecimal($currency)) {
            $amount*=100;
        }
        $request = [
            'currency' => strtolower($currency),
            'product' => $productId,
            'interval' => $interval,
            'interval_count' => $intervalNum,
            'amount' => round($amount)
        ];
        if ($nickName) {
            $request['nickname'] = $nickName;
        }
        if ($trialDay) {
            $request['trial_period_days'] = $trialDay;
        }
        $response = $this->_helper->sendRequest($request, 'https://api.stripe.com/v1/plans', null);
        return $response;
    }

    public function updatePlan($planId, $nickName = "", $trialDay = false)
    {
        $request = [];
        if ($nickName) {
            $request['nickname'] = $nickName;
        }
        if ($trialDay) {
            $request['trial_period_days'] = $trialDay;
        }
        $response = $this->_helper->sendRequest($request, 'https://api.stripe.com/v1/plans/'.$planId, null);
        return $response;
    }

    public function deletePlan($planId)
    {
        $response = $this->_helper->sendRequest(null, 'https://api.stripe.com/v1/plans/'.$planId, "delete");
        return $response;
    }

    public function isSubscriptionProduct($productId)
    {
        $model = $this->productAttributeFactory->create()->load($productId, "entity_id");
        if ($model->getId()) {
            return $model->getData('is_enabled');
        }
        return false;
    }

    public function getSubscriptionAdditionalOption($productId, $planId, $cycle = false)
    {
        $additionalOptions = [];
        $model = $this->productAttributeFactory->create()->load($productId, "entity_id");
        if ($model->getId()) {
            $arrValue = json_decode($model->getValue(), true);
            if (isset($arrValue[$planId])) {
                $option = $arrValue[$planId];
                $unitId = isset($option['unit_id']) ? $option['unit_id'] : "";
                $frequency = isset($option['frequency']) ? $option['frequency'] : "";
                $trialDay = false;
                $nickName = isset($option['plan_name']) ? $option['plan_name'] : "";
                if (isset($option['is_trial_enabled']) && ($option['is_trial_enabled'] == "1")) {
                    $trialDay = isset($option['trial_day']) ? $option['trial_day'] : false;
                }
                if ($nickName) {
                    $additionalOptions[] = [
                        'label' => __("Plan Name"),
                        'value' => $nickName
                    ];
                }
                $additionalOptions[] = [
                    'label' => __("Billing"),
                    'value' => __("every")." ".$frequency." ".$unitId
                ];
                if ($trialDay) {
                    $additionalOptions[] = [
                        'label' => __("Trial Day"),
                        'value' => $trialDay
                    ];
                }
                if ($cycle) {
                    $additionalOptions[] = [
                        'label' => __("Total Billing Cycle"),
                        'value' => $cycle
                    ];
                }
            }
        }
        return $additionalOptions;
    }

    public function encodeProductData($dataArr)
    {
        $version = $this->productMetadataInterface->getVersion();
        if (version_compare($version, "2.2.0") < 0) {
            return serialize($dataArr);
        } else {
            return json_encode($dataArr);
        }
    }

    public function decodeProductData($dataArr)
    {
        $version = $this->productMetadataInterface->getVersion();
        if (version_compare($version, "2.2.0") < 0) {
            return unserialize($dataArr);
        } else {
            return json_decode($dataArr, true);
        }
    }

    public function addSourceToStripe($infoInstance, $_cardID, $_paymentToken)
    {
        if ($this->customerSession->isLoggedIn()) {
            try {
                $customerModel = $this->_customerFactory->create();
                /** @var \Magenest\Stripe\Model\Customer $customer */
                //find customer in DB
                $customer = $customerModel->getCollection()
                    ->addFieldToFilter('magento_customer_id', $this->customerSession->getCustomerId())
                    ->getFirstItem();
                /**
                 * if customer registered and have data in db, get stripe cus_id
                 * else: create customer data
                 */
                $stripeCustomerId = null;
                if ($customer->getId()) {
                    //is a customer
                    $stripeCustomerId = $customer->getData('stripe_customer_id');
                    //check stripe customer id
                    $checkResp = $this->_helper->checkStripeCustomerId($stripeCustomerId);
                    $this->_debug($checkResp);
                    if (isset($checkResp['error'])) {
                        //delete old and create new customer
                        $customer->delete();
                        $stripeCustomerId = $this->createCustomer();
                    }
                } else {
                    /**
                     * create stripe customer
                     */
                    $stripeCustomerId = $this->createCustomer();
                }
                if ($_paymentToken != '0') {
                    $response = $this->addSourceToCustomer($stripeCustomerId, $_paymentToken);
                    $this->_debug($response);
                }
                if ($_cardID != "0") {
                    $infoInstance->setAdditionalInformation('payment_token', $_cardID);
                }
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($e->getMessage())
                );
            }
        }
    }


    public function changeCardToDefault($customerId, $cardId)
    {
        try {
            $cardModel = $this->_cardFactory->create();
            $check = $this->_cardFactory->create()
                ->getCollection()
                ->addFieldToFilter('card_id', $cardId)
                ->getFirstItem();
            if ($check->getId()) {
                $collections = $cardModel->getCollection()
                    ->addFieldToFilter("magento_customer_id", $customerId);

                foreach ($collections as $collection) {
                    if ($collection->getData()['status'] === "default") {
                        $collection->setData("status", "active");
                        $collection->save();
                    }
                }
                $check->setData("status", "default");
                $check->save();
                $cardModel->save();
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function addSourceToCustomer($stripeCustomerId, $source)
    {
        $request = [
            'source' => $source
        ];
        $url = 'https://api.stripe.com/v1/customers/' . $stripeCustomerId . '/sources';
        $response = $this->_helper->sendRequest($request, $url, 'post');
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface[]
     * @return bool
     */
    public function isSubscriptionOrder($orderItems)
    {
        foreach ($orderItems as $item) {
            $productOptions = $item->getData('product_options');
            $stripeSubscription = isset($productOptions['info_buyRequest']['stripe_subscription'])?$productOptions['info_buyRequest']['stripe_subscription']:[];
            if (isset($stripeSubscription['plan_id']) && $stripeSubscription['plan_id']) {
                return true;
            }
        }

        return false;
    }

    public function addCustomerDefaultSource($stripeCustomerId, $source)
    {
        $request = [
            'default_source' => $source
        ];
        $url = 'https://api.stripe.com/v1/customers/' . $stripeCustomerId;
        $response = $this->_helper->sendRequest($request, $url, 'post');
    }

    public function getPlanDataFromPlanId($planId, $productId = null)
    {
        if ($productId) {
            $model = $this->productAttributeFactory->create()->load($productId, "entity_id");
            if ($model->getId()) {
                $arrValue = json_decode($model->getValue(), true);
                if (isset($arrValue[$planId])) {
                    $option = $arrValue[$planId];
                    $unitId = isset($option['unit_id']) ? $option['unit_id'] : null;
                    $frequency = isset($option['frequency']) ? $option['frequency'] : null;
                    $trialDay = isset($option['trial_day']) ? $option['trial_day'] : null;
                    $nickName = isset($option['plan_name']) ? $option['plan_name'] : null;
                    return [
                        'interval' => $unitId,
                        'interval_count' => $frequency,
                        'trial_period_days' => $trialDay,
                        'nickname' => $nickName,
                    ];
                }
            }
        } else {
            $url = 'https://api.stripe.com/v1/plans/' . $planId;
            return $this->_helper->sendRequest(null, $url);
        }
        return [];
    }

    public function convertIntervalToDay($interval)
    {
        if (strtolower($interval) == 'day') {
            return 1;
        }
        if (strtolower($interval) == 'week') {
            return 7;
        }
        if (strtolower($interval) == 'month') {
            return 30;
        }
        if (strtolower($interval) == 'year') {
            return 365;
        }
        return 7;
    }

    public function createSubscription($request)
    {
        $url = 'https://api.stripe.com/v1/subscriptions';
        return $this->_helper->sendRequest($request, $url, 'post');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $subscriptionResponse
     */
    public function saveSubscriptionData($order, $subscriptionResponse)
    {
        $subscriptionItemData = $subscriptionResponse['items']['data'][0];
        $subscription = $this->subscriptionFactory->create();
        $subscription->setData('order_id', $order->getId());
        $subscription->setData('customer_id', $order->getCustomerId());
        $subscription->setData('subscription_id', $subscriptionResponse['id']);
        $subscription->setData('period_start', $subscriptionResponse['current_period_start']);
        $subscription->setData('period_end', $subscriptionResponse['current_period_end']);
        $subscription->setData('status', $subscriptionResponse['status']);
        $subscription->setData('cancel_at_period_end', $subscriptionResponse['cancel_at_period_end']);
        $subscription->setData('canceled_at', $subscriptionResponse['canceled_at']);
        $subscription->setData('created', $subscriptionResponse['created']);
        $subscription->setData('trial_start', $subscriptionResponse['trial_start']);
        $subscription->setData('trial_end', $subscriptionResponse['trial_end']);
        $subscription->setData('ended_at', $subscriptionResponse['ended_at']);
        $subscription->setData('sequence_order_ids', json_encode([$order->getId()]));
        $subscription->save();

        $subscriptionItem = $this->subscriptionItemFactory->create();
        $subscriptionItem->setData("id", $subscriptionItemData['id']);
        $subscriptionItem->setData("subscription_id", $subscriptionItemData['subscription']);
        $subscriptionItem->setData("plan", json_encode($subscriptionItemData['plan']));
        $subscriptionItem->setData("quantity", $subscriptionItemData['quantity']);
        $subscriptionItem->save();
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $subscription
     * @param array $stripeSubscriptionResponse
     */
    public function updateSubscriptionObjData($subscription, $subscriptionResponse)
    {
        $subscription->setData('period_start', $subscriptionResponse['current_period_start']);
        $subscription->setData('period_end', $subscriptionResponse['current_period_end']);
        $subscription->setData('status', $subscriptionResponse['status']);
        $subscription->setData('cancel_at_period_end', $subscriptionResponse['cancel_at_period_end']);
        $subscription->setData('canceled_at', $subscriptionResponse['canceled_at']);
        $subscription->setData('created', $subscriptionResponse['created']);
        $subscription->setData('trial_start', $subscriptionResponse['trial_start']);
        $subscription->setData('trial_end', $subscriptionResponse['trial_end']);
        $subscription->setData('ended_at', $subscriptionResponse['ended_at']);
        return $subscription;
    }

    public function getSubscriptionData($subscriptionId)
    {
        if ($subscriptionId) {
            $url = 'https://api.stripe.com/v1/subscriptions/' . $subscriptionId;
            return $this->_helper->sendRequest(null, $url);
        }
        return [];
    }

    public function getSubscription($subscriptionId)
    {
        return $this->subscriptionFactory->create()->load($subscriptionId, "subscription_id");
    }
}
