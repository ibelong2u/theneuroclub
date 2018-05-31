<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Paypal\Api;

use Aheadworks\Sarp\Model\SubscriptionEngine\Api\MapperInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Error\Handler as ErrorHandler;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\FilterManager;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Response\Processor as ResponseProcessor;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ConfigProxy;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NvpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Nvp
     */
    private $api;

    /**
     * @var CurlFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $curlFactoryMock;

    /**
     * @var ConfigProxy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paypalConfigProxyMock;

    /**
     * @var ResponseProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseProcessorMock;

    /**
     * @var ErrorHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $errorHandlerMock;

    /**
     * @var MapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mapperMock;

    /**
     * @var FilterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterManagerMock;

    /**
     * @var DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectFactoryMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->curlFactoryMock = $this->createMock(CurlFactory::class);
        $this->paypalConfigProxyMock = $this->createMock(ConfigProxy::class);
        $this->responseProcessorMock = $this->createMock(ResponseProcessor::class);
        $this->errorHandlerMock = $this->createMock(ErrorHandler::class);
        $this->mapperMock = $this->getMockForAbstractClass(MapperInterface::class);
        $this->filterManagerMock = $this->createMock(FilterManager::class);
        $this->dataObjectFactoryMock = $this->createMock(DataObjectFactory::class);
        $this->api = $objectManager->getObject(
            Nvp::class,
            [
                'curlFactory' => $this->curlFactoryMock,
                'paypalConfigProxy' => $this->paypalConfigProxyMock,
                'responseProcessor' => $this->responseProcessorMock,
                'errorHandler' => $this->errorHandlerMock,
                'mapper' => $this->mapperMock,
                'filterManager' => $this->filterManagerMock,
                'dataObjectFactory' => $this->dataObjectFactoryMock
            ]
        );
    }

    /**
     * Set up mocks for call() method
     *
     * @param string $methodName
     * @param DataObject|\PHPUnit_Framework_MockObject_MockObject $requestMock
     * @param DataObject|\PHPUnit_Framework_MockObject_MockObject $responseMock
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function setUpCall($methodName, $requestMock, $responseMock)
    {
        $requestData = ['field_name' => 'field_value'];
        $responseData = ['ACK' => 'SUCCESS'];
        $rawResponseData = 'raw response data';
        $timeout = 60;
        $verifyPeer = '1';
        $apiUserName = 'dummy@gmail.com';
        $apiPassword = 'dummy123';
        $apiSignature = 'dummysignature';
        $buildNotationCode = 'Magento_Cart_Community';

        $curlMock = $this->createMock(Curl::class);

        $this->curlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($curlMock);
        $this->paypalConfigProxyMock->expects($this->exactly(8))
            ->method('getValue')
            ->willReturnMap(
                [
                    ['verifyPeer', null, $verifyPeer],
                    ['use_proxy', null, false],
                    ['apiAuthentication', null, false], // called twice
                    ['sandboxFlag', null, false],
                    ['apiUsername', null, $apiUserName],
                    ['apiPassword', null, $apiPassword],
                    ['apiSignature', null, $apiSignature]
                ]
            );
        $curlMock->expects($this->once())
            ->method('setConfig')
            ->with(
                [
                    'timeout' => $timeout,
                    'verifypeer' => $verifyPeer
                ]
            );
        $curlMock->expects($this->once())
            ->method('write')
            ->with(
                'POST',
                'https://api-3t.paypal.com/nvp',
                '1.1',
                []
            );
        $requestMock->expects($this->once())
            ->method('getData')
            ->willReturn($requestData);
        $this->paypalConfigProxyMock->expects($this->once())
            ->method('__call')
            ->with('getBuildNotationCode')
            ->willReturn($buildNotationCode);
        $this->mapperMock->expects($this->once())
            ->method('toApi')
            ->with($methodName, $requestData)
            ->willReturnArgument(1);
        $this->filterManagerMock->expects($this->once())
            ->method('filterToApi')
            ->with(
                [
                    'field_name' => 'field_value',
                    'VERSION' => Nvp::VERSION,
                    'USER' => $apiUserName,
                    'PWD' => $apiPassword,
                    'SIGNATURE' => $apiSignature,
                    'BUTTONSOURCE' => $buildNotationCode
                ]
            )
            ->willReturnArgument(0);
        $curlMock->expects($this->once())
            ->method('read')
            ->willReturn($rawResponseData);
        $this->responseProcessorMock->expects($this->once())
            ->method('processRawResponse')
            ->with($rawResponseData)
            ->willReturn($responseData);
        $this->responseProcessorMock->expects($this->once())
            ->method('postProcessResponse')
            ->with($responseData)
            ->willReturnArgument(0);
        $curlMock->expects($this->once())
            ->method('getErrno')
            ->willReturn(0);
        $curlMock->expects($this->once())->method('close');
        $this->mapperMock->expects($this->once())
            ->method('fromApi')
            ->with($methodName, $responseData)
            ->willReturnArgument(1);
        $this->filterManagerMock->expects($this->once())
            ->method('filterFromApi')
            ->with($responseData)
            ->willReturnArgument(0);
        $this->dataObjectFactoryMock->expects($this->once())
            ->method('create')
            ->with($responseData)
            ->willReturn($responseMock);
    }

    public function testCallSetExpressCheckout()
    {
        $methodName = Nvp::SET_EXPRESS_CHECKOUT;
        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $requestMock $requestMock */
        $requestMock = $this->createMock(DataObject::class);
        $responseMock = $this->createMock(DataObject::class);
        $this->setUpCall($methodName, $requestMock, $responseMock);
        $this->assertEquals($responseMock, $this->api->callSetExpressCheckout($requestMock));
    }

    public function testCallGetExpressCheckoutDetails()
    {
        $methodName = Nvp::GET_EXPRESS_CHECKOUT_DETAILS;
        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $requestMock $requestMock */
        $requestMock = $this->createMock(DataObject::class);
        $responseMock = $this->createMock(DataObject::class);
        $this->setUpCall($methodName, $requestMock, $responseMock);
        $this->assertEquals($responseMock, $this->api->callGetExpressCheckoutDetails($requestMock));
    }

    public function testCallCreateRecurringPaymentsProfile()
    {
        $methodName = Nvp::CREATE_RECURRING_PAYMENTS_PROFILE;
        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $requestMock $requestMock */
        $requestMock = $this->createMock(DataObject::class);
        $responseMock = $this->createMock(DataObject::class);
        $this->setUpCall($methodName, $requestMock, $responseMock);
        $this->assertEquals($responseMock, $this->api->callCreateRecurringPaymentsProfile($requestMock));
    }

    public function testCallUpdateRecurringPaymentsProfile()
    {
        $methodName = Nvp::UPDATE_RECURRING_PAYMENTS_PROFILE;
        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $requestMock $requestMock */
        $requestMock = $this->createMock(DataObject::class);
        $responseMock = $this->createMock(DataObject::class);
        $this->setUpCall($methodName, $requestMock, $responseMock);
        $this->assertEquals($responseMock, $this->api->callUpdateRecurringPaymentsProfile($requestMock));
    }

    public function testCallGetRecurringPaymentsProfileDetails()
    {
        $methodName = Nvp::GET_RECURRING_PAYMENTS_PROFILE_DETAILS;
        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $requestMock $requestMock */
        $requestMock = $this->createMock(DataObject::class);
        $responseMock = $this->createMock(DataObject::class);
        $this->setUpCall($methodName, $requestMock, $responseMock);
        $this->assertEquals($responseMock, $this->api->callGetRecurringPaymentsProfileDetails($requestMock));
    }

    public function testCallManageRecurringPaymentsProfileStatus()
    {
        $methodName = Nvp::MANAGE_RECURRING_PAYMENTS_PROFILE_STATUS;
        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $requestMock $requestMock */
        $requestMock = $this->createMock(DataObject::class);
        $responseMock = $this->createMock(DataObject::class);
        $this->setUpCall($methodName, $requestMock, $responseMock);
        $this->assertEquals($responseMock, $this->api->callManageRecurringPaymentsProfileStatus($requestMock));
    }
}
