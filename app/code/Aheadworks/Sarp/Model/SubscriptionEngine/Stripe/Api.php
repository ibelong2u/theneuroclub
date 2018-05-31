<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe;

use Aheadworks\Sarp\Model\SubscriptionEngine\Api\MapperInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\ErrorHandler;
use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\FilterManager;
use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Request\Preprocessor;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Api
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Api
{
    /**
     * Api base url
     */
    const API_BASE_URL = 'https://api.stripe.com';

    /**
     * Api resources paths
     */
    const PATH_PLANS = 'plans';
    const PATH_CUSTOMERS = 'customers';
    const PATH_SUBSCRIPTIONS = 'subscriptions';
    const PATH_EVENTS = 'events';

    /**
     * Connection timeout
     */
    const CONNECTION_TIMEOUT = 60;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var Config
     */
    private $stripeConfig;

    /**
     * @var Preprocessor
     */
    private $requestPreprocessor;

    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var ErrorHandler
     */
    private $errorHandler;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param CurlFactory $curlFactory
     * @param Config $stripeConfig
     * @param Preprocessor $requestPreprocessor
     * @param MapperInterface $mapper
     * @param FilterManager $filterManager
     * @param ErrorHandler $errorHandler
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        CurlFactory $curlFactory,
        Config $stripeConfig,
        Preprocessor $requestPreprocessor,
        MapperInterface $mapper,
        FilterManager $filterManager,
        ErrorHandler $errorHandler
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->curlFactory = $curlFactory;
        $this->stripeConfig = $stripeConfig;
        $this->requestPreprocessor = $requestPreprocessor;
        $this->mapper = $mapper;
        $this->filterManager = $filterManager;
        $this->errorHandler = $errorHandler;
    }

    /**
     * Perform create a plan request
     *
     * @param DataObject $params
     * @return DataObject
     * @throws LocalizedException
     */
    public function requestCreatePlan(DataObject $params)
    {
        return $this->request(
            \Zend_Http_Client::POST,
            self::PATH_PLANS,
            sprintf('%s/v1/%s', self::API_BASE_URL, self::PATH_PLANS),
            $params
        );
    }

    /**
     * Perform update plan request
     *
     * @param int $planId
     * @param DataObject $params
     * @return DataObject
     * @throws LocalizedException
     */
    public function requestUpdatePlan($planId, DataObject $params)
    {
        return $this->request(
            \Zend_Http_Client::POST,
            self::PATH_PLANS,
            sprintf('%s/v1/%s/%s', self::API_BASE_URL, self::PATH_PLANS, $planId),
            $params
        );
    }

    /**
     * Perform create a customer request
     *
     * @param DataObject $params
     * @return DataObject
     * @throws LocalizedException
     */
    public function requestCreateCustomer(DataObject $params)
    {
        return $this->request(
            \Zend_Http_Client::POST,
            self::PATH_CUSTOMERS,
            sprintf('%s/v1/%s', self::API_BASE_URL, self::PATH_CUSTOMERS),
            $params
        );
    }

    /**
     * Perform update customer request
     *
     * @param int $customerId
     * @param DataObject $params
     * @return DataObject
     * @throws LocalizedException
     */
    public function requestUpdateCustomer($customerId, DataObject $params)
    {
        return $this->request(
            \Zend_Http_Client::POST,
            self::PATH_CUSTOMERS,
            sprintf('%s/v1/%s/%s', self::API_BASE_URL, self::PATH_CUSTOMERS, $customerId),
            $params
        );
    }

    /**
     * Perform create a subscription request
     *
     * @param DataObject $params
     * @return DataObject
     * @throws LocalizedException
     */
    public function requestCreateSubscription(DataObject $params)
    {
        return $this->request(
            \Zend_Http_Client::POST,
            self::PATH_SUBSCRIPTIONS,
            sprintf('%s/v1/%s', self::API_BASE_URL, self::PATH_SUBSCRIPTIONS),
            $params
        );
    }

    /**
     * Perform retrieve a subscription request
     *
     * @param int $subscriptionId
     * @return DataObject
     * @throws LocalizedException
     */
    public function requestRetrieveSubscription($subscriptionId)
    {
        return $this->request(
            \Zend_Http_Client::GET,
            self::PATH_SUBSCRIPTIONS,
            sprintf('%s/v1/%s/%s', self::API_BASE_URL, self::PATH_SUBSCRIPTIONS, $subscriptionId),
            $this->dataObjectFactory->create()
        );
    }

    /**
     * Perform update a subscription request
     *
     * @param int $subscriptionId
     * @param DataObject $params
     * @return DataObject
     * @throws LocalizedException
     */
    public function requestUpdateSubscription($subscriptionId, DataObject $params)
    {
        return $this->request(
            \Zend_Http_Client::POST,
            self::PATH_SUBSCRIPTIONS,
            sprintf('%s/v1/%s/%s', self::API_BASE_URL, self::PATH_SUBSCRIPTIONS, $subscriptionId),
            $params
        );
    }

    /**
     * Perform cancel a subscription request
     *
     * @param int $subscriptionId
     * @return DataObject
     * @throws LocalizedException
     */
    public function requestCancelSubscription($subscriptionId)
    {
        return $this->request(
            \Zend_Http_Client::DELETE,
            self::PATH_SUBSCRIPTIONS,
            sprintf('%s/v1/%s/%s', self::API_BASE_URL, self::PATH_SUBSCRIPTIONS, $subscriptionId),
            $this->dataObjectFactory->create()
        );
    }

    /**
     * Perform retrieve an event request
     *
     * @param int $eventId
     * @return DataObject
     * @throws LocalizedException
     */
    public function requestRetrieveEvent($eventId)
    {
        return $this->request(
            \Zend_Http_Client::GET,
            self::PATH_EVENTS,
            sprintf('%s/v1/%s/%s', self::API_BASE_URL, self::PATH_EVENTS, $eventId),
            $this->dataObjectFactory->create()
        );
    }

    /**
     * Perform api request
     *
     * @param string $method
     * @param string $resourcePath
     * @param string $url
     * @param DataObject $params
     * @return DataObject
     * @throws LocalizedException
     */
    private function request($method, $resourcePath, $url, DataObject $params)
    {
        $curl = $this->curlFactory->create();
        $curl->setConfig(['timeout' => self::CONNECTION_TIMEOUT, 'header' => false]);
        if ($method == \Zend_Http_Client::DELETE) {
            $curl->addOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        $curl->write(
            $method,
            $url,
            '1.1',
            $this->getHeaders(),
            $this->prepareRequestParams($params, $resourcePath)
        );

        try {
            $responseData = \Zend_Json::decode($curl->read());
        } catch (\Exception $e) {
            $curl->close();
            throw new LocalizedException(__('Unable to perform Stripe API request.'));
        }
        $responseCode = $curl->getInfo(CURLINFO_HTTP_CODE);
        $curl->close();

        if (!$this->isRequestSuccessful($responseCode)) {
            $this->errorHandler->handle($responseCode, $responseData);
        }

        return $this->importResponseData($responseData, $resourcePath);
    }

    /**
     * Get secret key
     *
     * @return string
     */
    private function getSecretKey()
    {
        $isTestMode = $this->stripeConfig->isTestMode();
        return $isTestMode
            ? $this->stripeConfig->getTestSecretKey()
            : $this->stripeConfig->getSecretKey();
    }

    /**
     * Get http headers
     *
     * @return array
     */
    private function getHeaders()
    {
        $headers = [];
        $headersData = [
            'Authorization' => 'Bearer ' . $this->getSecretKey(),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        foreach ($headersData as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }
        return $headers;
    }

    /**
     * Prepare request data
     *
     * @param DataObject $params
     * @param string $resourcePath
     * @return array
     */
    private function prepareRequestParams(DataObject $params, $resourcePath)
    {
        $data = $this->filterManager->filterToApi(
            $this->mapper->toApi($resourcePath, $params->getData())
        );
        return $this->requestPreprocessor->process($data);
    }

    /**
     * Import response data
     *
     * @param array $response
     * @param string $resourcePath
     * @return DataObject
     */
    private function importResponseData(array $response, $resourcePath)
    {
        $data = $this->filterManager->filterFromApi(
            $this->mapper->fromApi($resourcePath, $response)
        );
        /** @var DataObject $responseInstance */
        $responseInstance = $this->dataObjectFactory->create($data);
        return $responseInstance;
    }

    /**
     * Check if request successful
     *
     * @param array $code
     * @return bool
     */
    private function isRequestSuccessful($code)
    {
        return $code >= 200 && $code < 300;
    }
}
