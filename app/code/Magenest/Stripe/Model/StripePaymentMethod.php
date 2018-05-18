<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 11/05/2016
 * Time: 13:33
 */

namespace Magenest\Stripe\Model;

use Magenest\Stripe\Helper\Constant;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Payment\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magenest\Stripe\Helper\Data as DataHelper;
use Magenest\Stripe\Helper\Config as ConfigHelper;

class StripePaymentMethod extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'magenest_stripe';
    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canCaptureOnce = true;
    protected $_canAuthorize = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_cardFactory;
    protected $_helper;
    /**
     * @var \Magenest\Stripe\Helper\Logger $stripeLogger
     */
    public $stripeLogger;
    public $_config;
    protected $customerSession;
    protected $_messageManager;
    protected $storeManagerInterface;
    protected $request;
    protected $subscriptionHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        ModuleListInterface $moduleList,
        TimezoneInterface $localeDate,
        DataHelper $dataHelper,
        ConfigHelper $config,
        \Magenest\Stripe\Helper\Logger $stripeLogger,
        \Magenest\Stripe\Model\CardFactory $cardFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\App\RequestInterface $request,
        \Magenest\Stripe\Helper\SubscriptionHelper $subscriptionHelper,
        $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );

        $this->_cardFactory = $cardFactory;
        $this->_helper = $dataHelper;
        $this->_config = $config;
        $this->request = $request;
        $this->stripeLogger = $stripeLogger;
        $this->customerSession = $customerSession;
        $this->_messageManager = $messageManager;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function canUseInternal()
    {
        return $this->getConfigData('active_moto');
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return \Magento\Payment\Model\Method\AbstractMethod::isAvailable($quote);
    }

    public function validate()
    {
        return \Magento\Payment\Model\Method\AbstractMethod::validate();
    }

    public function isInitializeNeeded()
    {
        if ($this->_appState->getAreaCode() == 'adminhtml') {
            return false;
        } else {
            return true;
        }
    }

    public function hasVerification()
    {
        return true;
    }

    public function getConfigPaymentAction()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $orderItems = $order->getItems();
        if ($this->subscriptionHelper->isSubscriptionOrder($orderItems)) {
            return "authorize_capture";
        }
        return parent::getConfigPaymentAction(); // TODO: Change the autogenerated stub
    }
    public function canRefund()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $orderItems = $order->getItems();
        if ($this->subscriptionHelper->isSubscriptionOrder($orderItems)) {
            return false;
        }
        return parent::canRefund(); // TODO: Change the autogenerated stub
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $infoInstance = $this->getInfoInstance();
        $additionalData = $data->getData('additional_data');
        parent::assignData($data);
        if ($this->_appState->getAreaCode() == 'adminhtml') {
            $sourceId = isset($additionalData['source_id'])?$additionalData['source_id']:"";
            $customerId = isset($additionalData['customer_id'])?$additionalData['customer_id']:"";
            $infoInstance->setAdditionalInformation('source_id', $sourceId);
            $infoInstance->setAdditionalInformation('customer_id', $customerId);
            if ($sourceId) {
                $infoInstance->setAdditionalInformation('db_source', true);
            }
            return $this;
        }

        $stripeResponse = isset($additionalData['stripe_response'])?$additionalData['stripe_response']:"";
        $response = json_decode($stripeResponse, true);

        if ($response) {
            $thredDSecure = isset($response['card']['three_d_secure'])?$response['card']['three_d_secure']:"";
            $isSaveOption = isset($additionalData['saved']) ? $additionalData['saved'] : false;
            $sourceId = isset($response['id']) ? $response['id'] : false;
            $infoInstance->setAdditionalInformation('stripe_response', $stripeResponse);
            $infoInstance->setAdditionalInformation('three_d_secure', $thredDSecure);
            $cardID = "";
        } else {
            $sourceId = isset($additionalData['cardId']) ? $additionalData['cardId'] : false;
            $cardID = $sourceId;
            $isSaveOption = "0";
        }
        $infoInstance->setAdditionalInformation('save_option', $isSaveOption);
        $infoInstance->setAdditionalInformation('payment_token', $sourceId);
        $infoInstance->setAdditionalInformation('origin_source', $sourceId);

        $this->addPaymentInfoData($infoInstance, $cardID);
        return $this;
    }

    public function addPaymentInfoData($infoInstance, $_cardID)
    {
        if (!$_cardID) {
            $response = json_decode($infoInstance->getAdditionalInformation('stripe_response'), true);
            $cardData = isset($response['card'])?$response['card']:"";
        } else {
            $cardData = $this->_cardFactory->create()->getCollection()
                ->addFieldToFilter('card_id', $_cardID)
                ->getFirstItem()
                ->getData();
            $infoInstance->setAdditionalInformation('three_d_secure', $cardData['three_d_secure']);
            $infoInstance->setAdditionalInformation('db_source', true);
        }

        $infoInstance->addData(
            [
                'cc_type' => $cardData['brand'],
                'cc_last_4' => $cardData['last4'],
                'cc_exp_month' => $cardData['exp_month'],
                'cc_exp_year' => $cardData['exp_year']
            ]
        );
    }

    public function initialize($paymentAction, $stateObject)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $payment = $this->getInfoInstance();
        $payment->setAdditionalInformation(Constant::ADDITIONAL_PAYMENT_ACTION, $paymentAction);
        $order = $payment->getOrder();
        $orderItems = $order->getItems();
        if (($this->subscriptionHelper->isSubscriptionOrder($orderItems)) && ($order->getCustomerIsGuest())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You need create an account to order subscription product')
            );
        }
        $this->_debug("-------Stripe init: orderid: " . $order->getIncrementId());
        $stateObject->setIsNotified($order->getCustomerNoteNotify());
        $amount = $order->getBaseGrandTotal();
        $threeDSecureAction = $this->_config->getThreedsecure();
        $threeDSecureVerify = $this->_config->getThreeDSecureVerify();
        $threeDSecureVerify = explode(",", $threeDSecureVerify);
        $threeDSecureStatus = $payment->getAdditionalInformation("three_d_secure");
        //active 3d secure
        if ($threeDSecureAction == 1) {
            if (($threeDSecureStatus == "required") || (in_array($threeDSecureStatus, $threeDSecureVerify))) {
                $this->perform3dSecure($payment, $amount);
            } else {
                $stateObject->setData('state', \Magento\Sales\Model\Order::STATE_PROCESSING);
                $this->placeOrder($payment, $amount, $paymentAction);
            }
        } else {
            //not active
            $stateObject->setData('state', \Magento\Sales\Model\Order::STATE_PROCESSING);
            $this->placeOrder($payment, $amount, $paymentAction);
        }

        return parent::initialize($paymentAction, $stateObject); // TODO: Change the autogenerated stub
    }

    /**
     * Function place order for non-3ds payment
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function placeOrder($payment, $amount, $paymentAction)
    {
        $this->_debug("Place order action");
        $order = $payment->getOrder();
        $totalDue = $order->getTotalDue();
        $baseTotalDue = $order->getBaseTotalDue();

        //3d secure: false
        $payment->setAdditionalInformation(Constant::ADDITIONAL_THREEDS, "false");
        if ($paymentAction == 'authorize') {
            $payment->setAmountAuthorized($totalDue);
            $payment->authorize(true, $baseTotalDue);
        } else {
            $payment->setAmountAuthorized($totalDue);
            $payment->setBaseAmountAuthorized($baseTotalDue);
            $payment->capture(null);
        }
    }

    /**
     * Function order for 3d secure check
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function perform3dSecure(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->_debug("Order action, 3ds on");
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);
        $order->addStatusHistoryComment(__("Stripe 3d Secure"));
        $currency = $order->getBaseCurrencyCode();
        $multiply = 100;
        if ($this->_helper->isZeroDecimal($currency)) {
            $multiply = 1;
        }
        $_amount = $amount * $multiply;
        $cardSrc = $payment->getAdditionalInformation('payment_token');
        $returnUrl = $this->storeManagerInterface->getStore()->getBaseUrl() . "stripe/checkout/threedSecureResponse";

        $request = [
            "amount" => round($_amount),
            "currency" => strtoupper($currency),
            "type" => "three_d_secure",
            "three_d_secure" => array(
                "card" => $cardSrc,
            ),
            "redirect" => array(
                "return_url" => $returnUrl
            ),
        ];
        $source = $this->_helper->sendRequest($request, Constant::SOURCE_ENDPOINT, "post");
        $this->_debug($source);
        $clientSecret = $source['client_secret'];
        $redirectStatus = $source['redirect']['status'];

        $threeDSecureUrl = $source['redirect']['url'];
        //3d secure: true
        $payment->setAdditionalInformation(Constant::ADDITIONAL_THREEDS, "true");
        $payment->setAdditionalInformation("threed_secure_url", $threeDSecureUrl);
        $payment->setAdditionalInformation("client_secret", $clientSecret);
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->_debug("authorize action");
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        if ($this->_appState->getAreaCode() == 'adminhtml') {
            $paymentToken = $this->_helper->getDirectSource($order);
        } else {
            $paymentToken = $payment->getAdditionalInformation('payment_token');
        }

        try {
            $dbSource = $payment->getAdditionalInformation('db_source');
            $customerId = $payment->getAdditionalInformation('customer_id');
            $stripeCustomerId = $this->_helper->getStripeCustomerId($customerId);
            $request = $this->_helper->createChargeRequest($order, $amount, $paymentToken, false, $dbSource, $stripeCustomerId);
            $this->_debug($request);
            $url = 'https://api.stripe.com/v1/charges';
            $response = $this->_helper->sendRequest($request, $url, null);
            $this->_debug($response);
            if (isset($response['error'])) {
                $this->_messageManager->addErrorMessage("Message: ".isset($response['error']['message'])?$response['error']['message']:"");
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Payment error')
                );
            }
            if (isset($response['status']) && ($response['status'] == 'succeeded')) {
                $order->setCanSendNewEmailFlag(true);
                $_saved = $payment->getAdditionalInformation('save_option');

                if ($_saved == "1") {
                    if (($this->customerSession->isLoggedIn())) {
                        $stripeResponse = json_decode($payment->getAdditionalInformation('stripe_response'), true);
                        $this->_helper->saveCard($order->getCustomerId(), $stripeResponse);
                    }
                }
                $payment->setAmount($amount);
                $payment->setTransactionId($response['id'])
                    ->setIsTransactionClosed(false)
                    ->setShouldCloseParentTransaction(false)
                    ->setCcTransId($response['id']);

                $this->_helper->saveCharge($order, $response, "authorized");
            } else {
                throw new \Exception(
                    __("Payment exception")
                );
            }
        } catch (\Exception $e) {
            $this->stripeLogger->critical($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }

        return $this;
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $this->_debug("capture action");
            /** @var \Magento\Sales\Model\Order $order */
            $order = $payment->getOrder();
            $transId = $payment->getCcTransId();
            if ($transId) {
                $url = Constant::CHARGE_ENDPOINT.'/' . $transId . '/capture';
                $request = $this->_helper->createCaptureRequest($order, $amount);
                $response = $this->_helper->sendRequest($request, $url, null);
                $this->_debug($response);
                if (isset($response['error'])) {
                    $this->_messageManager->addErrorMessage("Message: ".$response['error']['message']);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Payment error')
                    );
                }
                if (isset($response['status'])&&($response['status'] == 'succeeded')) {
                    $transactionId = isset($response['balance_transaction'])?$response['balance_transaction']:"";
                    $payment->setStatus(\Magento\Payment\Model\Method\AbstractMethod::STATUS_SUCCESS)
                        ->setShouldCloseParentTransaction(1)
                        ->setIsTransactionClosed(1);
                    $payment->setTransactionId($transactionId);
                } else {
                    throw new \Magenest\Stripe\Exception\StripePaymentException(
                        __('Capture fail')
                    );
                }
            } else {
                //call capture api
                if ($this->_appState->getAreaCode() == 'adminhtml') {
                    $paymentToken = $this->_helper->getDirectSource($order);
                } else {
                    $paymentToken = $payment->getAdditionalInformation('payment_token');
                }
                $dbSource = $payment->getAdditionalInformation('db_source');
                $customerId = $payment->getAdditionalInformation('customer_id');
                $stripeCustomerId = $this->_helper->getStripeCustomerId($customerId);
                //check subscription order
                $orderItems = $order->getItems();
                if ($this->subscriptionHelper->isSubscriptionOrder($orderItems)) {
                    //call subscription api
                    $payment->setAdditionalInformation("do_subscription_action", true);
                    //$this->subscriptionModel->createSubscription($payment, $paymentToken);
                    return parent::capture($payment, $amount);
                }

                $request = $this->_helper->createChargeRequest($order, $amount, $paymentToken, true, $dbSource, $stripeCustomerId);
                $this->_debug($request);
                $url = 'https://api.stripe.com/v1/charges';
                $response = $this->_helper->sendRequest($request, $url, null);
                $this->_debug($response);
                if (isset($response['error'])) {
                    $this->_messageManager->addErrorMessage("Message: ".$response['error']['message']);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Payment error')
                    );
                }
                if (isset($response['status']) && ($response['status'] == 'succeeded')) {
                    $order->setCanSendNewEmailFlag(true);
                    $_saved = $payment->getAdditionalInformation('save_option');

                    if ($_saved == "1") {
                        if (($this->customerSession->isLoggedIn())) {
                            $stripeResponse = json_decode($payment->getAdditionalInformation('stripe_response'), true);
                            $this->_helper->saveCard($order->getCustomerId(), $stripeResponse);
                        }
                    }
                    $payment->setAmount($amount);
                    $payment->setTransactionId($response['id'])
                        ->setIsTransactionClosed(false)
                        ->setShouldCloseParentTransaction(false)
                        ->setCcTransId($response['id']);

                    $this->_helper->saveCharge($order, $response, "captured");
                } else {
                    throw new \Exception(
                        __("Payment exception")
                    );
                }
            }
        } catch (\Exception $e) {
            $this->stripeLogger->debug($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
        return parent::capture($payment, $amount);
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->_debug("refund action");
        try {
            $refundReason = $this->request->getParam('refund_reason');
            /** @var \Magento\Sales\Model\Order $order */
            $order = $payment->getOrder();
            $multiply = 100;
            if ($this->_helper->isZeroDecimal($order->getBaseCurrencyCode())) {
                $multiply = 1;
            }
            $_amount = $amount * $multiply;
            $transId = $payment->getCcTransId();
            if ($transId) {
                $url = 'https://api.stripe.com/v1/refunds';

                $request = [
                    'charge' => $transId,
                    'amount' => round($_amount)
                ];
                if ($refundReason) {
                    $request['reason'] = $refundReason;
                }

                $response = $this->_helper->sendRequest($request, $url, null);
                $this->_debug($response);
                if (isset($response['error'])) {
                    $this->_messageManager->addErrorMessage("Message: ".$response['error']['message']);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Refund error')
                    );
                }
                if ($response['status'] == 'succeeded') {
                    $transactionId = isset($response['balance_transaction'])?$response['balance_transaction']:"";
                    $payment->setTransactionId($transactionId);
                    $payment->setShouldCloseParentTransaction(0);
                    $this->_messageManager->addSuccessMessage("Balance Transaction: ".$transactionId);
                }
                if ($response['status'] == 'failed') {
                    $failTransactionId = isset($response['failure_balance_transaction'])?$response['failure_balance_transaction']:"";
                    $failReason = isset($response['failure_reason'])?$response['failure_reason']:"";
                    $this->_messageManager->addErrorMessage("Failure Reason: ".$failReason);
                    $this->_messageManager->addErrorMessage("Failure Balance Transaction: ".$failTransactionId);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Refund error')
                    );
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Charge doesn\'t exist. Please try again later.')
                );
            }
        } catch (\Exception $e) {
            $this->stripeLogger->critical($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Refund exception')
            );
        }

        return $this;
    }

    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        $this->_debug("void action");
        /** @var \Magento\Sales\Model\Order $order */
        try {
            $transId = $payment->getCcTransId();
            if ($transId) {
                $url = 'https://api.stripe.com/v1/refunds';

                $request = [
                    'charge' => $transId
                ];

                $response = $this->_helper->sendRequest($request, $url, null);
                $this->_debug($response);
                if (isset($response['error'])) {
                    $this->_messageManager->addErrorMessage("Message: ".$response['error']['message']);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Refund error')
                    );
                }
                if (isset($response['status']) && ($response['status'] == 'succeeded')) {
                    $payment->setShouldCloseParentTransaction(1);
                    $payment->setIsTransactionClosed(1);
                }
                if (isset($response['status']) && ($response['status'] == 'failed')) {
                    $failReason = isset($response['failure_reason'])?$response['failure_reason']:"";
                    $this->_messageManager->addErrorMessage("Failure Reason: ".$failReason);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Refund error')
                    );
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Charge doesn\'t exist. Please try again later.')
                );
            }
        } catch (\Exception $e) {
            $this->stripeLogger->critical($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Void exception')
            );
        }

        return $this;
    }

    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $this->_debug("void action");
        /** @var \Magento\Sales\Model\Order $order */
        try {
            $transId = $payment->getCcTransId();
            if ($transId) {
                $url = 'https://api.stripe.com/v1/refunds';

                $request = [
                    'charge' => $transId
                ];

                $response = $this->_helper->sendRequest($request, $url, null);
                $this->_debug($response);
                if (isset($response['error'])) {
                    $this->_messageManager->addErrorMessage("Message: ".$response['error']['message']);
                }
                if (isset($response['status']) && ($response['status'] == 'succeeded')) {
                    $payment->setShouldCloseParentTransaction(1);
                    $payment->setIsTransactionClosed(1);
                }
                if (isset($response['status']) && ($response['status'] == 'failed')) {
                    $failReason = isset($response['failure_reason'])?$response['failure_reason']:"";
                    $this->_messageManager->addErrorMessage("Failure Reason: ".$failReason);
                }
            } else {
            }
        } catch (\Exception $e) {
            $this->stripeLogger->critical($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Void exception')
            );
        }

        return $this;
    }

    /**
     * @param array|string $debugData
     */
    protected function _debug($debugData)
    {
        $debugOn = $this->getDebugFlag();
        if ($debugOn === true) {
            $this->stripeLogger->debug(var_export($debugData, true));
        }
    }
}
