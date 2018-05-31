<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api;

use Aheadworks\Sarp\Model\SubscriptionEngine\Api\MapperInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Error\Handler as ErrorHandler;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\FilterManager;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Response\Processor as ResponseProcessor;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ConfigProxy;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;

/**
 * Class Nvp
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Nvp
{
    const SET_EXPRESS_CHECKOUT = 'SetExpressCheckout';
    const GET_EXPRESS_CHECKOUT_DETAILS = 'GetExpressCheckoutDetails';
    const CREATE_RECURRING_PAYMENTS_PROFILE = 'CreateRecurringPaymentsProfile';
    const UPDATE_RECURRING_PAYMENTS_PROFILE = 'UpdateRecurringPaymentsProfile';
    const GET_RECURRING_PAYMENTS_PROFILE_DETAILS = 'GetRecurringPaymentsProfileDetails';
    const MANAGE_RECURRING_PAYMENTS_PROFILE_STATUS = 'ManageRecurringPaymentsProfileStatus';

    /**
     * API version
     */
    const VERSION = '72.0';

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var ConfigProxy
     */
    private $paypalConfigProxy;

    /**
     * @var ResponseProcessor
     */
    private $responseProcessor;

    /**
     * @var ErrorHandler
     */
    private $errorHandler;

    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param CurlFactory $curlFactory
     * @param ConfigProxy $paypalConfigProxy
     * @param ResponseProcessor $responseProcessor
     * @param ErrorHandler $errorHandler
     * @param MapperInterface $mapper
     * @param FilterManager $filterManager
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        CurlFactory $curlFactory,
        ConfigProxy $paypalConfigProxy,
        ResponseProcessor $responseProcessor,
        ErrorHandler $errorHandler,
        MapperInterface $mapper,
        FilterManager $filterManager,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->curlFactory = $curlFactory;
        $this->paypalConfigProxy = $paypalConfigProxy;
        $this->responseProcessor = $responseProcessor;
        $this->errorHandler = $errorHandler;
        $this->mapper = $mapper;
        $this->filterManager = $filterManager;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Call SetExpressCheckout
     *
     * @param DataObject $request
     * @return DataObject
     * @throws LocalizedException
     * @throws \Exception
     */
    public function callSetExpressCheckout(DataObject $request)
    {
        return $this->call(self::SET_EXPRESS_CHECKOUT, $request);
    }

    /**
     * Call GetExpressCheckoutDetails
     *
     * @param DataObject $request
     * @return DataObject
     * @throws LocalizedException
     * @throws \Exception
     */
    public function callGetExpressCheckoutDetails(DataObject $request)
    {
        return $this->call(self::GET_EXPRESS_CHECKOUT_DETAILS, $request);
    }

    /**
     * Call CreateRecurringPaymentsProfile
     *
     * @param DataObject $request
     * @return DataObject
     * @throws LocalizedException
     * @throws \Exception
     */
    public function callCreateRecurringPaymentsProfile(DataObject $request)
    {
        return $this->call(self::CREATE_RECURRING_PAYMENTS_PROFILE, $request);
    }

    /**
     * Call UpdateRecurringPaymentsProfile
     *
     * @param DataObject $request
     * @return DataObject
     * @throws LocalizedException
     * @throws \Exception
     */
    public function callUpdateRecurringPaymentsProfile(DataObject $request)
    {
        return $this->call(self::UPDATE_RECURRING_PAYMENTS_PROFILE, $request);
    }

    /**
     * Call GetRecurringPaymentsProfileDetails
     *
     * @param DataObject $request
     * @return DataObject
     * @throws LocalizedException
     * @throws \Exception
     */
    public function callGetRecurringPaymentsProfileDetails(DataObject $request)
    {
        return $this->call(self::GET_RECURRING_PAYMENTS_PROFILE_DETAILS, $request);
    }

    /**
     * Call ManageRecurringPaymentsProfileStatus
     *
     * @param DataObject $request
     * @return DataObject
     * @throws LocalizedException
     * @throws \Exception
     */
    public function callManageRecurringPaymentsProfileStatus(DataObject $request)
    {
        return $this->call(self::MANAGE_RECURRING_PAYMENTS_PROFILE_STATUS, $request);
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
        /** @var Curl $curl */
        $curl = $this->curlFactory->create();
        try {
            $curl->setConfig($this->getCurlConfig());
            $curl->write(
                \Zend_Http_Client::POST,
                $this->getEndpoint(),
                '1.1',
                [],
                http_build_query($this->exportRequestData($request, $methodName))
            );
            $rawResponse = $curl->read();
        } catch (\Exception $e) {
            throw $e;
        }

        $response = $this->responseProcessor->processRawResponse($rawResponse);
        $response = $this->responseProcessor->postProcessResponse($response);

        if ($curl->getErrno()) {
            $curl->close();
            throw new LocalizedException(
                __('Payment Gateway is unreachable at the moment. Please use another payment option.')
            );
        }

        $curl->close();

        if (!$this->isCallSuccessful($response)) {
            $this->errorHandler->handleCallErrors($response);
        }

        return $this->importResponseData($response, $methodName);
    }

    /**
     * Get config for CURL adapter
     *
     * @return array
     */
    private function getCurlConfig()
    {
        $config = [
            'timeout' => 60,
            'verifypeer' => $this->paypalConfigProxy->getValue('verifyPeer')
        ];
        if ($this->paypalConfigProxy->getValue('use_proxy')) {
            $config['proxy'] = $this->paypalConfigProxy->getValue('proxy_host') . ':'
                . $this->paypalConfigProxy->getValue('proxy_port');
        }
        if ($this->paypalConfigProxy->getValue('apiAuthentication')) {
            $config['ssl_cert'] = $this->paypalConfigProxy->getApiCertificate();
        }
        return $config;
    }

    /**
     * Get api endpoint
     *
     * @return string
     */
    private function getEndpoint()
    {
        $urlTemplate = $this->paypalConfigProxy->getValue('apiAuthentication')
            ? 'https://api%s.paypal.com/nvp'
            : 'https://api-3t%s.paypal.com/nvp';
        return sprintf($urlTemplate, $this->paypalConfigProxy->getValue('sandboxFlag') ? '.sandbox' : '');
    }

    /**
     * Check if call successful
     *
     * @param array $response
     * @return bool
     */
    private function isCallSuccessful($response)
    {
        if (isset($response['ACK'])) {
            $ack = strtoupper($response['ACK']);
            return $ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING';
        }
        return false;
    }

    /**
     * Prepare request data
     *
     * @param DataObject $request
     * @param string $methodName
     * @return array
     */
    private function exportRequestData(DataObject $request, $methodName)
    {
        $data = array_merge(
            $this->mapper->toApi($methodName, $request->getData()),
            [
                'VERSION' => self::VERSION,
                'USER' => $this->paypalConfigProxy->getValue('apiUsername'),
                'PWD' => $this->paypalConfigProxy->getValue('apiPassword'),
                'SIGNATURE' => $this->paypalConfigProxy->getValue('apiSignature'),
                'BUTTONSOURCE' => $this->paypalConfigProxy->getBuildNotationCode()
            ]
        );
        return $this->filterManager->filterToApi($data);
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
