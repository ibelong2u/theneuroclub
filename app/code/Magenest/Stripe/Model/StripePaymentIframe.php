<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 01/01/2017
 * Time: 00:08
 */

namespace Magenest\Stripe\Model;

use Magenest\Stripe\Helper\Constant;
use Magento\Payment\Model\Method\AbstractMethod;

class StripePaymentIframe extends AbstractMethod
{
    const CODE = 'magenest_stripe_iframe';
    protected $_code = self::CODE;

    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canCaptureOnce = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_isInitializeNeeded = true;
    protected $_canOrder = true;

    protected $stripeCard;
    protected $_helper;
    public $_config;
    public $stripeLogger;

    public function __construct(
        \Magenest\Stripe\Helper\Data $dataHelper,
        StripePaymentMethod $stripePaymentMethod,
        \Magenest\Stripe\Helper\Logger $stripeLogger,
        \Magenest\Stripe\Helper\Config $config,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_helper = $dataHelper;
        $this->stripeCard = $stripePaymentMethod;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_config = $config;
        $this->stripeLogger = $stripeLogger;
    }

    public function validate()
    {
        return \Magento\Payment\Model\Method\AbstractMethod::validate();
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $additionalData = $data->getData('additional_data');
        $stripeResponse = isset($additionalData['stripe_response'])?$additionalData['stripe_response']:"";
        $response = json_decode($stripeResponse, true);
        $infoInstance = $this->getInfoInstance();
        if ($response) {
            $thredDSecure = isset($response['card']['three_d_secure'])?$response['card']['three_d_secure']:"";
            $sourceId = isset($response['id']) ? $response['id'] : false;
            $payType = isset($response['type']) ? $response['type'] : "";
            if ($payType != 'card') {
                throw new \Exception(
                    __("Operation not allowed")
                );
            }
            $infoInstance->setAdditionalInformation('stripe_response', $stripeResponse);
            $infoInstance->setAdditionalInformation('three_d_secure', $thredDSecure);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong. Please try again later.')
            );
        }
        $infoInstance->setAdditionalInformation('payment_token', $sourceId);

        return $this;
    }

    public function initialize($paymentAction, $stateObject)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $payment = $this->getInfoInstance();
        $payment->setAdditionalInformation(Constant::ADDITIONAL_PAYMENT_ACTION, $paymentAction);
        $order = $payment->getOrder();
        $this->_debug("-------Stripe checkout.js init: orderid: " . $order->getIncrementId());
        $stateObject->setIsNotified($order->getCustomerNoteNotify());
        $amount = $order->getBaseGrandTotal();
        $threeDSecureAction = $this->_config->getThreedsecure();
        $threeDSecureVerify = $this->_config->getThreeDSecureVerify();
        $threeDSecureVerify = explode(",", $threeDSecureVerify);
        $threeDSecureStatus = $payment->getAdditionalInformation("three_d_secure");
        //active 3d secure
        if ($threeDSecureAction == 1) {
            if (($threeDSecureStatus == "required") || (in_array($threeDSecureStatus, $threeDSecureVerify))) {
                $this->order($payment, $amount);
            } else {
                $stateObject->setData('state', \Magento\Sales\Model\Order::STATE_PROCESSING);
                $this->stripeCard->placeOrder($payment, $amount, $paymentAction);
            }
        } else {
            //not active
            $stateObject->setData('state', \Magento\Sales\Model\Order::STATE_PROCESSING);
            $this->stripeCard->placeOrder($payment, $amount, $paymentAction);
        }

        return parent::initialize($paymentAction, $stateObject);
    }

    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->stripeCard->order($payment, $amount);
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->stripeCard->authorize($payment, $amount);
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->stripeCard->capture($payment, $amount);
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->stripeCard->refund($payment, $amount);
    }

    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->stripeCard->void($payment);
    }

    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->stripeCard->cancel($payment);
    }

    /**
     * Function place order bitcoin
     * @deprecated
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function bitcoinPlaceOrder($payment)
    {
        $order = $payment->getOrder();
        $totalDue = $order->getTotalDue();
        $baseTotalDue = $order->getBaseTotalDue();
        $isSubscriptionOrder = $this->stripeCard->isSubscriptionOrder($payment);
        if ($isSubscriptionOrder) {
            $this->_logger->debug("Place order fail");
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Place order fail')
            );
        }
        $payment->setAmountAuthorized($totalDue);
        $payment->setBaseAmountAuthorized($baseTotalDue);
        $payment->capture(null);
    }

    /**
     * Function capture bitcoin payment
     * @deprecated
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function bitcoinCapture($payment, $amount)
    {
        $paymentToken = $payment->getAdditionalInformation('payment_token'); // THIS IS THE TOKEN
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        try {
            $request = [
                "amount" => round($amount * 100),
                "currency" => $order->getBaseCurrencyCode(),
                'capture' => 'true',
                "source" => $paymentToken
            ];

            $url = 'https://api.stripe.com/v1/charges';
            $response = $this->_helper->sendRequest($request, $url, null);
            $this->_logger->debug(serialize($response));
            if ($response['status'] == 'succeeded') {
                $payment->setAmount($amount);
                $payment->setTransactionId($response['id'])
                    ->setParentTransactionId($response['id'])
                    ->setIsTransactionClosed(false)
                    ->setShouldCloseParentTransaction(false)
                    ->setCcTransId($response['id'])
                    ->setLastTransId($response['id']);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong. Please try again later.')
                );
            }
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong. Please try again later.')
            );
        }
    }

    protected function _debug($debugData)
    {
        $debugOn = $this->getDebugFlag();
        if ($debugOn === true) {
            $this->stripeLogger->debug(var_export($debugData, true));
        }
    }
}
