<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 25/05/2016
 * Time: 16:46
 */

namespace Magenest\Stripe\Model;

use Magenest\Stripe\Helper\Constant;
use Magenest\Stripe\Model\ResourceModel\Subscription as Resource;
use Magenest\Stripe\Model\ResourceModel\Subscription\Collection as Collection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order;
use Magenest\Stripe\Helper\Data as DataHelper;

class Subscription extends AbstractModel
{
    protected $_eventPrefix = 'subscription_';

    protected $orderFactory;

    protected $_orderManagement;

    protected $stripeHelperData;

    protected $subscriptionHelper;

    protected $stripeConfig;

    protected $stripeLogger;

    protected $_helper;

    public function __construct(
        Context $context,
        Registry $registry,
        Resource $resource,
        Collection $resourceCollection,
        OrderFactory $orderFactory,
        DataHelper $dataHelper,
        OrderManagementInterface $orderManagement,
        \Magenest\Stripe\Helper\Data $stripeHelperData,
        \Magenest\Stripe\Helper\SubscriptionHelper $subscriptionHelper,
        \Magenest\Stripe\Helper\Config $stripeConfig,
        \Magenest\Stripe\Helper\Logger $stripeLogger,
        $data = []
    ) {
        $this->_helper = $dataHelper;
        $this->orderFactory = $orderFactory;
        $this->_orderManagement = $orderManagement;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->stripeHelperData = $stripeHelperData;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->stripeConfig = $stripeConfig;
        $this->stripeLogger = $stripeLogger;
    }

    public function placeOrder()
    {
        /** @var \Magento\Sales\Model\Order $newOrder */
        try {
            $newOrder = $this->generateOrder();
            $newOrder->save();
        } catch (\Exception $e) {
            return false;
        }


        return $newOrder;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function sendInvoice($order)
    {
        $payment = $order->getPayment();
        $payment->setTransactionId($this->getData('subscription_id'))->setIsTransactionClosed(0);
        $payment->registerCaptureNotification($order->getGrandTotal());
        $invoice = $payment->getCreatedInvoice();

        $order->save();
    }

    public function generateOrder()
    {
        //the order id of first related order of the subscription
        $orderId = $this->getData('order_id');

        if ($orderId) {
            /** @var  \Magento\Sales\Model\Order $order */
            $order = $this->orderFactory->create()->load($orderId);

            $newOrder = $this->orderFactory->create();
            $orderInfo = $order->getData();
            try {
                $objectManager = ObjectManager::getInstance();

                /** @var \Magenest\Stripe\Helper\Config $configModel */
                $configModel = $objectManager->create('\Magenest\Stripe\Helper\Config');

                $billingAdd = $objectManager->create('Magento\Sales\Model\Order\Address');
                $oriBA = $order->getBillingAddress()->getData();
                $billingAdd->setData($oriBA)->setId(null);

                if ($order->getShippingAddress()) {
                    $shippingAdd = $objectManager->create('Magento\Sales\Model\Order\Address');
                    $shippingInfo = $order->getBillingAddress()->getData();
                    $shippingAdd->setData($shippingInfo)->setId(null);
                } else {
                    $shippingAdd = null;
                }
                /** @var \Magento\Sales\Model\Order\Payment $payment */
                $payment = $objectManager->create('Magento\Sales\Model\Order\Payment');
                $paymentMethodCode = $order->getPayment()->getMethod();

                $payment->setMethod($paymentMethodCode);

                $transferDataKays = array(
                    'store_id',
                    'store_name',
                    'customer_id',
                    'customer_email',
                    'customer_firstname',
                    'customer_lastname',
                    'customer_middlename',
                    'customer_prefix',
                    'customer_suffix',
                    'customer_taxvat',
                    'customer_gender',
                    'customer_is_guest',
                    'customer_note_notify',
                    'customer_group_id',
                    'customer_note',
                    'shipping_method',
                    'shipping_description',
                    'base_currency_code',
                    'global_currency_code',
                    'order_currency_code',
                    'store_currency_code',
                    'base_to_global_rate',
                    'base_to_order_rate',
                    'store_to_base_rate',
                    'store_to_order_rate'
                );


                foreach ($transferDataKays as $key) {
                    if (isset($orderInfo[$key])) {
                        $newOrder->setData($key, $orderInfo[$key]);
                    } elseif (isset($shippingInfo[$key])) {
                        $newOrder->setData($key, $shippingInfo[$key]);
                    }
                }

                $storeId = $order->getStoreId();
                $newOrder->setStoreId($storeId)
                    ->setState(Order::STATE_NEW)
                    ->setStatus($configModel->getNewOrderStatus())
                    ->setBaseToOrderRate($order->getBaseToOrderRate())
                    ->setStoreToOrderRate($order->getStoreToOrderRate())
                    ->setOrderCurrencyCode($order->getOrderCurrencyCode())
                    ->setBaseSubtotal($order->getBaseSubtotal())
                    ->setSubtotal($order->getSubtotal())
                    ->setBaseShippingAmount($order->getBaseShippingAmount())
                    ->setShippingAmount($order->getShippingAmount())
                    ->setBaseTaxAmount($order->getBaseTaxAmount())
                    ->setTaxAmount($order->getTaxAmount())
                    ->setBaseGrandTotal($order->getBaseGrandTotal())
                    ->setGrandTotal($order->getGrandTotal())
                    ->setIsVirtual($order->getIsVirtual())
                    ->setWeight($order->getWeight())
                    //->setTotalQtyOrdered($this->getInfoValue('order_info', 'items_qty'))
                    ->setTotalQtyOrdered($order->getTotalQtyOrdered())
                    ->setBillingAddress($billingAdd)
                    ->setShippingAddress($shippingAdd)
                    ->setPayment($payment);

                //todo
                /** @var \Magento\Sales\Model\Order\Item[] $items */
                $items = $order->getAllItems();
                foreach ($items as $item) {
                    $newOrderItem = clone $item;
                    $newOrderItem->setId(null);
                    $newOrderItem->setQtyShipped(0);
                    $newOrder->addItem($newOrderItem);
                }
            } catch (\Exception $e) {
            }

            return $newOrder;
        }

        return null;
    }

    public function addSequenceOrder($orderId)
    {
        $sequenceOrderIds = '';
        $sequenceOrderIds = $this->getData('sequence_order_ids');

        if (!$sequenceOrderIds) {
            $this->addData([
                "sequence_order_ids" => serialize([$orderId])
            ])->save();
        } else {
            $sequenceOrderIds = unserialize($sequenceOrderIds);
            array_push($sequenceOrderIds, $orderId);

            $this->addData([
                "sequence_order_ids" => serialize($sequenceOrderIds)
            ])->save();
        }
    }

    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     */
    public function createSubscription($payment, $sourceId, $dbSource = false)
    {
        $payment->setAdditionalInformation("do_subscription_action", false);
        $order = $payment->getOrder();
        $stripeResponse = json_decode($payment->getAdditionalInformation('stripe_response'), true);
        $stripeCustomerId = $this->stripeHelperData->getStripeCustomerId();
        $originSourceId = $payment->getAdditionalInformation('origin_source');
        if (!$dbSource) {
            //new customer
            $stripeCustomerId = $this->stripeHelperData->saveCard($order->getCustomerId(), $stripeResponse);
        } else {
            //old customer
            //check customer
            if ($stripeCustomerId) {
                if (!$this->stripeHelperData->checkStripeCustomerId($stripeCustomerId)) {
                    $this->stripeHelperData->deleteStripeCustomerId($stripeCustomerId);
                    $stripeCustomerId = $this->stripeHelperData->createCustomer($sourceId);
                }
            }
        }
        $_res = $this->stripeHelperData->changeCustomerSourceToDefault($stripeCustomerId, $originSourceId);
        if (isset($_res['error'])) {
            throw new \Exception(
                __("Payment error")
            );
        }
        $subscriptionBilling = $this->stripeConfig->getSubscriptionBilling();
        $subscriptionDayDue = $this->stripeConfig->getSubscriptionBillingDayDue();
        $subscriptionApplyTax = $this->stripeConfig->getSubscriptionApplyTax();
        $items = $order->getAllVisibleItems();
        $item = reset($items);
        $taxPercent = $item->getData('tax_percent');
//        $qtyOrder = $item->getData('qty_ordered');
//        $productOptions = $item->getData('product_options');
//        $stripeSubscription = isset($productOptions['info_buyRequest']['stripe_subscription'])?$productOptions['info_buyRequest']['stripe_subscription']:[];
//        $planId = "";
//        if (isset($stripeSubscription['plan_id']) && $stripeSubscription['plan_id']) {
//            $planId = $stripeSubscription['plan_id'];
//        } else {
//            throw new \Exception(
//                __("Subscription Plan is not existed")
//            );
//        }
        $subscriptionMetadata = [
            //'order_id' => $order->getIncrementId(),
            'magento_customer_id' => $order->getCustomerId(),
            'customer_email' => $order->getCustomerEmail()
        ];
        $request = [
            'customer' => $stripeCustomerId,
            'billing' => $subscriptionBilling,
//            'items[0][plan]' => $planId,
//            'items[0][quantity]' => intval($qtyOrder),
            'source' => $originSourceId,
        ];
        if ($subscriptionBilling == 'send_invoice') {
            $request['days_until_due'] = $subscriptionDayDue;
        }
        if ($subscriptionApplyTax) {
            $request['tax_percent'] = $taxPercent;
        }
        $countSub = 0;
        foreach ($items as $item) {
            $qtyOrder = $item->getData('qty_ordered');
            $productOptions = $item->getData('product_options');
            $stripeSubscription = isset($productOptions['info_buyRequest']['stripe_subscription'])?$productOptions['info_buyRequest']['stripe_subscription']:[];
            $planId = "";
            if (isset($stripeSubscription['plan_id']) && $stripeSubscription['plan_id']) {
                $planId = $stripeSubscription['plan_id'];
                $request['items['.$countSub.']'.'[plan]'] = $planId;
                $request['items['.$countSub.']'.'[quantity]'] = $qtyOrder;
                $countSub++;
            }
        }
//        $is3DSecure = $payment->getAdditionalInformation(Constant::ADDITIONAL_THREEDS);
//        $planData = $this->subscriptionHelper->getPlanDataFromPlanId($planId);
//        if(isset($planData['error'])){
//            throw new \Exception(
//                __("Subscription Plan is not existed")
//            );
//        }
//        $firstChargeId = false;
//        if (!$planData['trial_period_days']) {
//            if ($is3DSecure == "true") {
//                //additional step to subscription
//                //normal charge
//                $amount = $order->getBaseGrandTotal();
//                $url = 'https://api.stripe.com/v1/charges';
//                $requestCharge = $this->stripeHelperData->createChargeRequest($order, $amount, $sourceId, true, true);
//                $requestCharge['metadata']['subscription_charge'] = 1;
//                $response = $this->stripeHelperData->sendRequest($requestCharge, $url, null);
//                $this->_debug($response);
//                if (isset($response['error'])) {
//                    throw new \Magento\Framework\Exception\LocalizedException(
//                        __('Payment error')
//                    );
//                }
//                if (isset($response['status'])&&($response['status'] == 'succeeded')) {
//                    $transactionId = isset($response['balance_transaction'])?$response['balance_transaction']:"";
//                    $payment->setStatus(\Magento\Payment\Model\Method\AbstractMethod::STATUS_SUCCESS)
//                        ->setShouldCloseParentTransaction(1)
//                        ->setIsTransactionClosed(1);
//                    $payment->setTransactionId($transactionId);
//
//                    //convert subscription to trial
//                    $intervalCount = $planData['interval_count'];
//                    $intervalDay = $this->subscriptionHelper->convertIntervalToDay($planData['interval']);
//                    $trialDays = $intervalDay*$intervalCount;
//
//                    $request['trial_period_days'] = $trialDays;
//                    $firstChargeId = $response['id'];
//                    $subscriptionMetadata['first_charge_id'] = $firstChargeId;
//                } else {
//                    throw new \Magento\Framework\Exception\LocalizedException(
//                        __('Capture fail')
//                    );
//                }
//            }
//        }
        $request['metadata'] = $subscriptionMetadata;
        $subscriptionResponse = $this->subscriptionHelper->createSubscription($request);
        $this->_debug($subscriptionResponse);
        if (isset($subscriptionResponse['error'])) {
            //refund charge if exist.
//            if ($firstChargeId) {
//                $response = $this->stripeHelperData->sendRequest([
//                    'charge' => $firstChargeId
//                ], 'https://api.stripe.com/v1/refunds', null);
//            }
            $message = isset($subscriptionResponse['error']['message'])?$subscriptionResponse['error']['message']:"Subscription error";
            throw new \Magento\Framework\Exception\LocalizedException(
                __($message)
            );
        }
        if (isset($subscriptionResponse['id'])) {
            //add subscription record to system
            $invoices = $this->_helper->getAllInvoices($stripeCustomerId, $subscriptionResponse['id']);
            if (isset($invoices['error'])) {
                $invoices = [];
            }
             $this->subscriptionHelper->saveSubscriptionData($order, $subscriptionResponse, $invoices);
        }
    }

    protected function _debug($debugData)
    {
        $this->stripeLogger->debug(var_export($debugData, true));
    }
}
