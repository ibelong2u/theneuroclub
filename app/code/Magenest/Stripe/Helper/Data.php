<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 20/05/2016
 * Time: 12:01
 */

namespace Magenest\Stripe\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magenest\Stripe\Model\CustomerFactory;
use Magenest\Stripe\Helper\Config;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_encryptor;

    protected $_httpClientFactory;

    protected $_customerFactory;

    protected $_config;

    protected $_cardFactory;

    protected $_chargeFactory;

    protected $stripeLogger;

    protected $customerSession;

    public function __construct(
        Context $context,
        EncryptorInterface $encryptorInterface,
        ZendClientFactory $clientFactory,
        CustomerFactory $customerFactory,
        Config $config,
        \Magenest\Stripe\Model\CardFactory $cardFactory,
        \Magenest\Stripe\Model\ChargeFactory $chargeFactory,
        \Magenest\Stripe\Helper\Logger $stripeLogger,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_encryptor = $encryptorInterface;
        $this->_httpClientFactory = $clientFactory;
        $this->_customerFactory = $customerFactory;
        $this->_config = $config;
        parent::__construct($context);
        $this->_cardFactory = $cardFactory;
        $this->_chargeFactory = $chargeFactory;
        $this->stripeLogger = $stripeLogger;
        $this->customerSession = $customerSession;
    }

    /**
     * @param string $url
     * @param array $requestPost
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendRequestDelete($url, $requestPost = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $key = $this->_config->getSecretKey();
        //$httpHeaders = new \Zend\Http\Headers();
        $httpHeaders = $objectManager->create('\Zend\Http\Headers');
        $httpHeaders->addHeaders([
            'Authorization' => 'Bearer ' . $key,
        ]);
        //$request = new \Zend\Http\Request();
        $request = $objectManager->create('\Zend\Http\Request');
        $request->setHeaders($httpHeaders);
        $request->setUri($url);
        $request->setMethod(\Zend\Http\Request::METHOD_DELETE);

        if (!!$requestPost) {
            $request->getPost()->fromArray($requestPost);
        }

        //$client = new \Zend\Http\Client();
        $client = $objectManager->create('\Zend\Http\Client');
        $options = [
            'adapter' => 'Zend\Http\Client\Adapter\Curl',
            'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
            'maxredirects' => 0,
            'timeout' => 30
        ];
        $client->setOptions($options);
        try {
            $response = $client->send($request);
            $responseBody = $response->getBody();
            $responseBody = (array)json_decode($responseBody);

            return $responseBody;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Cannot send request to Stripe servers.')
            );
        }
    }

    public function sendRequest($requestPost, $url, $requestMethod = null)
    {
        if (!$requestMethod) {
            $requestMethod="post";
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $key = $this->_config->getSecretKey();
        //$httpHeaders = new \Zend\Http\Headers();
        $httpHeaders = $objectManager->create('\Zend\Http\Headers');
        $httpHeaders->addHeaders([
            'Authorization' => 'Bearer ' . $key,
        ]);
        //$request = new \Zend\Http\Request();
        $request = $objectManager->create('\Zend\Http\Request');
        $request->setHeaders($httpHeaders);
        $request->setUri($url);
        $request->setMethod(strtoupper($requestMethod));

        if (!!$requestPost) {
            $request->getPost()->fromArray($requestPost);
        }

        //$client = new \Zend\Http\Client();
        $client = $objectManager->create('\Zend\Http\Client');
        $options = [
            'adapter' => 'Zend\Http\Client\Adapter\Curl',
            'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
            'maxredirects' => 0,
            'timeout' => 30
        ];
        $client->setOptions($options);
        try {
            $response = $client->send($request);
            $responseBody = $response->getBody();
            $responseBody = json_decode($responseBody, true);

            return $responseBody;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Cannot send request to Stripe servers.')
            );
        }
    }

    public function deleteSubscription($subsId)
    {
        $isCancelAtPeriodEnd = $this->_config->getIsCancelAtPeriodEnd();

        $url = "https://api.stripe.com/v1/subscriptions/" . $subsId;
        if ($isCancelAtPeriodEnd) {
            $request = ['at_period_end' => "true"];
        } else {
            $request = [];
        }

        return $this->sendRequest($request, $url, "delete");
    }

    public function deleteSubscriptionCron($response)
    {
        $isTest = $this->scopeConfig->getValue(
            'payment/magenest_stripe/test',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $subsId = $response['id'];

        $testSecret = $this->_config->getConfigValue('test_secret');
        $liveSecret = $this->_config->getConfigValue('live_secret');

        $url = "https://api.stripe.com/v1/subscriptions/" . $subsId;
        $body = $this->sendRequestDelete($url, ['at_period_end' => "true"]);

        return $body;
    }

    public function calculateCurrentPeriod($response)
    {
        if ($response['status'] == 'active') {
            $numberOfPeriodsPassed = 0;
            if ($response['trial_end']) {
                $mainStart = $response['trial_end'];
            } else {
                $mainStart = $response['created'];
            }

            $periodInterval = $response['current_period_end'] - $response['current_period_start'];
            $intervalFromStart = $response['current_period_start'] - $mainStart;

            $numberOfPeriodsPassed = round($intervalFromStart / $periodInterval);

            return $numberOfPeriodsPassed++;
        } else {
            return 0;
        }
    }

    public function checkStripeCustomerId($cusId)
    {
        $url = 'https://api.stripe.com/v1/customers/' . $cusId;
        $request = $this->sendRequest([], $url, null);
        if (isset($request['error'])) {
            return false;
        }
        return true;
    }

    public function isZeroDecimal($currency)
    {
        return in_array(strtolower($currency), [
            'bif',
            'djf',
            'jpy',
            'krw',
            'pyg',
            'vnd',
            'xaf',
            'xpf',
            'clp',
            'gnf',
            'kmf',
            'mga',
            'rwf',
            'vuv',
            'xof'
        ]);
    }

    /**
     * @param string $customerId
     * @param $stripeResponse
     */
    public function saveCard($customerId, $stripeResponse)
    {
        try {
            $cardData = isset($stripeResponse['card'])?$stripeResponse['card']:[];
            $expMonth = isset($cardData['exp_month'])?$cardData['exp_month']:"";
            $expYear = isset($cardData['exp_year'])?$cardData['exp_year']:"";
            $brand = isset($cardData['brand'])?$cardData['brand']:"";
            $cardLast4 = isset($cardData['last4'])?$cardData['last4']:"";
            $sourceId = isset($stripeResponse['id'])?$stripeResponse['id']:"";
            $threeDSecureStatus = isset($cardData['three_d_secure'])?$cardData['three_d_secure']:"";
            $cardModel = $this->_cardFactory->create();
            $data = [
                'magento_customer_id' => $customerId,
                'card_id' => $sourceId,
                'brand' => $brand,
                'last4' => (string)$cardLast4,
                'exp_month' => (string)$expMonth,
                'exp_year' => (string)$expYear,
                'status' => "active",
                'three_d_secure' => $threeDSecureStatus
            ];

            $stripeCustomerId = $this->getStripeCustomerId();
            if ($stripeCustomerId) {
                if (!$this->checkStripeCustomerId($stripeCustomerId)) {
                    $this->deleteStripeCustomerId($stripeCustomerId);
                    $stripeCustomerId = $this->createCustomer($sourceId);
                } else {
                    $res = $this->addSourceToCustomer($stripeCustomerId, $sourceId);
                }
            } else {
                $stripeCustomerId = $this->createCustomer($sourceId);
            }

            if ($stripeCustomerId) {
                $cardModel->addData($data)->save();
            }
            return $stripeCustomerId;
        } catch (\Exception $e) {
            $this->stripeLogger->critical("save card exception". $e->getMessage());
            return false;
        }
    }

    public function deleteCard($customerId, $cardId)
    {

        $url = "https://api.stripe.com/v1/customers/".$customerId."/sources/" . $cardId;
        return $this->sendRequest([], $url, "delete");
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $response
     */
    public function saveCharge($order, $response, $type)
    {
        $chargeModel  = $this->_chargeFactory->create();
        $data = [
            'charge_id' => $response['id'],
            'order_id' => $order->getIncrementId(),
            'customer_id' => $order->getCustomerId(),
            'status' => $type
        ];

        $chargeModel->addData($data)->save();
    }

    /**
     * Create a stripe customer object
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $token
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createCustomer($source = null)
    {
        try {
            $customerModel = $this->_customerFactory->create();

            $url = 'https://api.stripe.com/v1/customers';

            $request = [
                "description" => $this->customerSession->getCustomer()->getEmail(),
                "email" => $this->customerSession->getCustomer()->getEmail()
            ];
            if ($source) {
                $request['source'] = $source;
            }

            $customer = $this->sendRequest($request, $url, null);
            $customerModel->addData([
                'magento_customer_id' => $this->customerSession->getCustomerId(),
                'stripe_customer_id' => $customer['id']
            ]);
            $customerModel->save();
            return $customer['id'];
        } catch (\Exception $e) {
            $this->stripeLogger->critical("create customer fail");
            return false;
        }
    }

    public function addSourceToCustomer($stripeCustomerId, $source)
    {
        $request = [
            'source' => $source
        ];
        $url = 'https://api.stripe.com/v1/customers/' . $stripeCustomerId . '/sources';
        $response = $this->sendRequest($request, $url, 'post');
    }

    public function getStripeCustomerId($magentoCustomerId = false)
    {
        if ($magentoCustomerId) {
            $customerId = $magentoCustomerId;
        } else {
            $customerId = $this->customerSession->getCustomerId();
        }
        $customer = $this->_customerFactory->create()->getCollection()
            ->addFieldToFilter('magento_customer_id', $customerId)
            ->getFirstItem();
        return $customer->getData('stripe_customer_id');
    }

    public function deleteStripeCustomerId($stripeCustomerId, $isOnline = false)
    {
        $customer = $this->_customerFactory->create()->getCollection()
            ->addFieldToFilter('stripe_customer_id', $stripeCustomerId)
            ->getFirstItem();
        return $customer->delete();
    }


    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $paymentToken
     * @param bool $isCapture
     */
    public function createChargeRequest($order, $amount, $paymentToken, $isCapture, $dbSource = false, $_stripeCustomerId = false)
    {
        $multiply = 100;
        if ($this->isZeroDecimal($order->getBaseCurrencyCode())) {
            $multiply = 1;
        }
        $amount = $amount * $multiply;
        $request = [
            "amount" => round($amount),
            "currency" => $order->getBaseCurrencyCode(),
            'capture' => $isCapture?'true':'false',
            "source" => $paymentToken,
            "description" => "Order Id: " . $order->getIncrementId(),
            "metadata" => [
                'order_id' => $order->getIncrementId(),
                'magento_customer_id' => $order->getCustomerId(),
                'customer_email' => $order->getCustomerEmail()
            ]
        ];
        if ($dbSource) {
            if ($_stripeCustomerId) {
                $stripeCustomer = $_stripeCustomerId;
            } else {
                $stripeCustomer = $this->getStripeCustomerId();
            }
            if ($stripeCustomer) {
                $request['customer'] = $stripeCustomer;
            }
        }
        if ($this->_config->sendMailCustomer()) {
            $request['receipt_email'] = $order->getCustomerEmail();
        }
        return $request;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function createCaptureRequest($order, $amount)
    {
        $multiply = 100;
        if ($this->isZeroDecimal($order->getBaseCurrencyCode())) {
            $multiply = 1;
        }
        $amount = $amount * $multiply;
        $request = [
            "amount" => round($amount),
        ];
        if ($this->_config->sendMailCustomer()) {
            $request['receipt_email'] = $order->getCustomerEmail();
        }
        return $request;
    }

    /**
     * @var \Magento\Sales\Model\Order $order
     */
    public function getDirectSource($order)
    {
        /** @var \Magento\Sales\Model\Order\Address $billing */
        $payment = $order->getPayment();
        $sourceId = $payment->getAdditionalInformation("source_id");
        if ($sourceId) {
            return $sourceId;
        }
        $billing = $order->getBillingAddress();
        $source = [
            'exp_month' => $order->getPayment()->getCcExpMonth(),
            'exp_year' => $order->getPayment()->getCcExpYear(),
            'number' => $order->getPayment()->getCcNumber(),
            'cvc' => $order->getPayment()->getCcCid(),
            'object' => 'card',
            'name' => $billing->getName(),
            'address_line1' => $billing->getStreetLine(1),
            'address_line2' => $billing->getStreetLine(2),
            'address_city' => $billing->getCity(),
            'address_zip' => $billing->getCity(),
            'address_state' => $billing->getRegion(),
            'address_country' => $billing->getCountryId()
        ];
        return $source;
    }

    public function changeCustomerSourceToDefault($customerId, $sourceId)
    {
        $url = 'https://api.stripe.com/v1/customers/'.$customerId;
        $request['default_source'] = $sourceId;
        return $this->sendRequest($request, $url, 'post');
    }

    public function getAllInvoices($customerId = null, $subscriptionId = null)
    {
        $request = [];
        if ($customerId) {
            $request['customer'] = $customerId;
        }
        if ($subscriptionId) {
            $request['subscription'] = $subscriptionId;
        }
        $url = "https://api.stripe.com/v1/invoices?".http_build_query(['customer'=>$customerId, 'subscription'=>$subscriptionId]);
        return $this->sendRequest([], $url, "get");
    }

    public function getSaveCard($customerId)
    {
        $col = $this->_cardFactory->create()->getCollection();
        $col->addFieldToFilter("magento_customer_id", $customerId);
        return $col;
    }
}
