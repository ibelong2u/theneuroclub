<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet;

use Aheadworks\Sarp\Model\SubscriptionEngine\Api\MapperInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\ErrorHandler;
use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\FilterManager;
use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\RequestBuilder;
use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\ResponseProcessor;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;

/**
 * Class Api
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Api
{
    /**
     * Request method codes
     */
    const CREATE_SUBSCRIPTION_REQUEST = 'ARBCreateSubscriptionRequest';
    const GET_SUBSCRIPTION_STATUS_REQUEST = 'ARBGetSubscriptionStatusRequest';
    const GET_SUBSCRIPTION_REQUEST = 'ARBGetSubscriptionRequest';
    const UPDATE_SUBSCRIPTION_REQUEST = 'ARBUpdateSubscriptionRequest';
    const CANCEL_SUBSCRIPTION_REQUEST = 'ARBCancelSubscriptionRequest';

    /**
     * Connection timeout
     */
    const CONNECTION_TIMEOUT = 45;

    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var Config
     */
    private $authorizeConfig;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var ResponseProcessor
     */
    private $responseProcessor;

    /**
     * @var ErrorHandler
     */
    private $errorHandler;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * @param ZendClientFactory $httpClientFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param Config $authorizeConfig
     * @param RequestBuilder $requestBuilder
     * @param ResponseProcessor $responseProcessor
     * @param ErrorHandler $errorHandler
     * @param FilterManager $filterManager
     * @param MapperInterface $mapper
     */
    public function __construct(
        ZendClientFactory $httpClientFactory,
        DataObjectFactory $dataObjectFactory,
        Config $authorizeConfig,
        RequestBuilder $requestBuilder,
        ResponseProcessor $responseProcessor,
        ErrorHandler $errorHandler,
        FilterManager $filterManager,
        MapperInterface $mapper
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->authorizeConfig = $authorizeConfig;
        $this->requestBuilder = $requestBuilder;
        $this->responseProcessor = $responseProcessor;
        $this->errorHandler = $errorHandler;
        $this->filterManager = $filterManager;
        $this->mapper = $mapper;
    }

    /**
     * Call ARBCreateSubscriptionRequest
     *
     * @param DataObject $request
     * @return DataObject
     * @throws LocalizedException
     * @throws \Exception
     */
    public function callARBCreateSubscriptionRequest(DataObject $request)
    {
        return $this->call(self::CREATE_SUBSCRIPTION_REQUEST, $request);
    }

    /**
     * Call ARBGetSubscriptionStatusRequest
     *
     * @param DataObject $request
     * @return DataObject
     * @throws LocalizedException
     * @throws \Exception
     */
    public function callARBGetSubscriptionStatusRequest(DataObject $request)
    {
        return $this->call(self::GET_SUBSCRIPTION_STATUS_REQUEST, $request);
    }

    /**
     * Call ARBGetSubscriptionRequest
     *
     * @param DataObject $request
     * @return DataObject
     * @throws LocalizedException
     * @throws \Exception
     */
    public function callARBGetSubscriptionRequest(DataObject $request)
    {
        return $this->call(self::GET_SUBSCRIPTION_REQUEST, $request);
    }

    /**
     * Call ARBUpdateSubscriptionRequest
     *
     * @param DataObject $request
     * @return DataObject
     * @throws LocalizedException
     * @throws \Exception
     */
    public function callARBUpdateSubscriptionRequest(DataObject $request)
    {
        return $this->call(self::UPDATE_SUBSCRIPTION_REQUEST, $request);
    }

    /**
     * Call ARBCancelSubscriptionRequest
     *
     * @param DataObject $request
     * @return DataObject
     * @throws LocalizedException
     * @throws \Exception
     */
    public function callARBCancelSubscriptionRequest(DataObject $request)
    {
        return $this->call(self::CANCEL_SUBSCRIPTION_REQUEST, $request);
    }

    /**
     * Call api method
     *
     * @param string $methodName
     * @param DataObject $request
     * @return DataObject
     * @throws LocalizedException
     * @throws \Exception
     */
    private function call($methodName, DataObject $request)
    {
        /** @var ZendClient $client */
        $client = $this->httpClientFactory->create();
        $client->setUri($this->getEndpoint());
        $client->setConfig(['timeout' => self::CONNECTION_TIMEOUT]);
        $client->setHeaders(['Content-Type: text/xml']);
        $client->setMethod(\Zend_Http_Client::POST);
        $client->setRawData($this->buildRequestBody($request, $methodName));

        try {
            $rawResponse = $client->request()->getBody();
        } catch (\Exception $e) {
            throw new LocalizedException(__('Unable to perform %1 call', $methodName));
        }

        $response = $this->responseProcessor->process($rawResponse);
        if (!$this->isCallSuccessful($response)) {
            $this->errorHandler->handle($response);
        }

        return $this->importResponseData($response, $methodName);
    }

    /**
     * Get api endpoint
     *
     * @return string
     */
    private function getEndpoint()
    {
        return sprintf(
            'https://api%s.authorize.net/xml/v1/request.api',
            $this->authorizeConfig->isTestMode() ? 'test' : ''
        );
    }

    /**
     * Check if call successful
     *
     * @param array $response
     * @return bool
     */
    private function isCallSuccessful($response)
    {
        if (isset($response['messages']['resultCode'])) {
            return $response['messages']['resultCode'] == 'Ok';
        }
        return false;
    }

    /**
     * Build request body
     *
     * @param DataObject $request
     * @param string $methodName
     * @return string
     */
    private function buildRequestBody(DataObject $request, $methodName)
    {
        $data = array_merge_recursive(
            [
                'merchantAuthentication' => [
                    'name' => $this->authorizeConfig->getApiLoginId(),
                    'transactionKey' => $this->authorizeConfig->getTransactionKey()
                ]
            ],
            $this->mapper->toApi($methodName, $request->getData())
        );
        return $this->requestBuilder->build(
            $methodName,
            $this->filterManager->filterToApi($data)
        );
    }

    /**
     * Import response data
     *
     * @param array $response
     * @param string $methodName
     * @return DataObject
     */
    private function importResponseData(array $response, $methodName)
    {
        $data = $this->filterManager->filterFromApi(
            $this->mapper->fromApi($methodName, $response)
        );
        /** @var DataObject $responseInstance */
        $responseInstance = $this->dataObjectFactory->create($data);
        return $responseInstance;
    }
}
