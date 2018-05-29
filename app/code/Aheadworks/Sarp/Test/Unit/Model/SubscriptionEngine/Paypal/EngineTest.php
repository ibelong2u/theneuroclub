<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Paypal;

use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp as ApiNvp;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataResolver;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Engine;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Engine
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EngineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var ApiNvp|\PHPUnit_Framework_MockObject_MockObject
     */
    private $apiMock;

    /**
     * @var DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectFactoryMock;

    /**
     * @var DataResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineDataResolverMock;

    /**
     * @var Copy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectCopyServiceMock;

    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessorMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->apiMock = $this->createMock(ApiNvp::class);
        $this->dataObjectFactoryMock = $this->createMock(DataObjectFactory::class);
        $this->engineDataResolverMock = $this->createMock(DataResolver::class);
        $this->objectCopyServiceMock = $this->createMock(Copy::class);
        $this->dataObjectProcessorMock = $this->createMock(DataObjectProcessor::class);
        $this->engine = $objectManager->getObject(
            Engine::class,
            [
                'api' => $this->apiMock,
                'dataObjectFactory' => $this->dataObjectFactoryMock,
                'engineDataResolver' => $this->engineDataResolverMock,
                'objectCopyService' => $this->objectCopyServiceMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock
            ]
        );
    }

    /**
     * Set up mocks for exportData() and ImportData() methods
     *
     * @param ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock
     * @param DataObject|\PHPUnit_Framework_MockObject_MockObject $requestMock
     * @param string $exportAspect
     * @param DataObject|\PHPUnit_Framework_MockObject_MockObject $responseMock
     * @param string $importAspect
     * @return void
     */
    private function setUpExportImportData(
        $profileMock,
        $requestMock,
        $exportAspect,
        $responseMock,
        $importAspect
    ) {
        $profileDescription = 'Recurring profile for product: Product Name';
        $shippingAddressData = ['address_type' => 'shipping'];
        $billingAddressData = ['address_type' => 'billing'];

        $shippingAddressMock = $this->getMockForAbstractClass(ProfileAddressInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(ProfileAddressInterface::class);

        $this->objectCopyServiceMock->expects($this->exactly(2))
            ->method('copyFieldsetToTarget')
            ->withConsecutive(
                [
                    'aw_sarp_convert_api_paypal_request',
                    $exportAspect,
                    $profileMock,
                    $requestMock
                ],
                [
                    'aw_sarp_convert_profile',
                    $importAspect,
                    $responseMock,
                    $profileMock
                ]
            );
        $profileMock->expects($this->once())
            ->method('getIsTrialPeriodEnabled')
            ->willReturn(false);
        $profileMock->expects($this->once())
            ->method('getIsInitialFeeEnabled')
            ->willReturn(false);
        $this->engineDataResolverMock->expects($this->once())
            ->method('getProfileDescription')
            ->with($profileMock)
            ->willReturn($profileDescription);
        $requestMock->expects($this->exactly(3))
            ->method('__call')
            ->withConsecutive(
                ['setProfileDescription', [$profileDescription]],
                ['setShippingAddress', [$shippingAddressData]],
                ['setBillingAddress', [$billingAddressData]]
            );
        $profileMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$shippingAddressMock, $billingAddressMock]);
        $this->dataObjectProcessorMock->expects($this->exactly(2))
            ->method('buildOutputDataArray')
            ->withConsecutive(
                [$shippingAddressMock, ProfileAddressInterface::class],
                [$billingAddressMock, ProfileAddressInterface::class]
            )
            ->willReturnOnConsecutiveCalls($shippingAddressData, $billingAddressData);
        $profileMock->expects($this->exactly(2))
            ->method('getIsCartVirtual')
            ->willReturn(false);
        $shippingAddressMock->expects($this->once())
            ->method('getAddressType')
            ->willReturn(Address::TYPE_SHIPPING);
        $billingAddressMock->expects($this->once())
            ->method('getAddressType')
            ->willReturn(Address::TYPE_BILLING);
    }

    public function testSubmitProfile()
    {
        $token = 'token_value';

        /** @var ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        $requestMock = $this->createMock(DataObject::class);
        $responseMock = $this->createMock(DataObject::class);

        $this->setUpExportImportData(
            $profileMock,
            $requestMock,
            'from_profile_while_create',
            $responseMock,
            'from_api_paypal_response_while_create'
        );
        $this->dataObjectFactoryMock->expects($this->once())
            ->method('create')
            ->with(['token' => $token])
            ->willReturn($requestMock);
        $this->apiMock->expects($this->once())
            ->method('callCreateRecurringPaymentsProfile')
            ->with($requestMock)
            ->willReturn($responseMock);

        $this->assertEquals($profileMock, $this->engine->submitProfile($profileMock, ['token' => $token]));
    }

    public function testUpdateProfile()
    {
        /** @var ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        $requestMock = $this->createMock(DataObject::class);
        $responseMock = $this->createMock(DataObject::class);

        $this->setUpExportImportData(
            $profileMock,
            $requestMock,
            'from_profile_while_update',
            $responseMock,
            'from_api_paypal_response_while_update'
        );
        $this->dataObjectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($requestMock);
        $this->apiMock->expects($this->once())
            ->method('callUpdateRecurringPaymentsProfile')
            ->with($requestMock)
            ->willReturn($responseMock);

        $this->assertEquals($profileMock, $this->engine->updateProfile($profileMock));
    }

    public function testGetProfileData()
    {
        $referenceId = 'profile_reference_id';
        $responseData = ['responseFieldName' => 'responseValue'];

        $requestMock = $this->createMock(DataObject::class);
        $responseMock = $this->createMock(DataObject::class);
        $apiResponseMock = $this->createMock(DataObject::class);

        $this->dataObjectFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive([['profile_id' => $referenceId]], [])
            ->willReturnOnConsecutiveCalls($requestMock, $responseMock);
        $this->objectCopyServiceMock->expects($this->once())
            ->method('copyFieldsetToTarget')
            ->with(
                'aw_sarp_convert_profile',
                'from_api_paypal_response_while_get',
                $apiResponseMock,
                $responseMock
            )
            ->willReturn($responseMock);
        $this->apiMock->expects($this->once())
            ->method('callGetRecurringPaymentsProfileDetails')
            ->with($requestMock)
            ->willReturn($apiResponseMock);
        $responseMock->expects($this->once())
            ->method('getData')
            ->willReturn($responseData);

        $this->assertEquals($responseData, $this->engine->getProfileData($referenceId));
    }

    public function testChangeStatus()
    {
        $referenceId = 'profile_reference_id';
        $action = 'activate';
        $expectedStatus = 'active';

        $requestMock1 = $this->createMock(DataObject::class);
        $requestMock2 = $this->createMock(DataObject::class);
        $responseMock = $this->createMock(DataObject::class);
        $apiResponseMock = $this->createMock(DataObject::class);

        $this->dataObjectFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->withConsecutive(
                [['profile_id' => $referenceId, 'action' => $action]],
                [['profile_id' => $referenceId]],
                []
            )
            ->willReturnOnConsecutiveCalls($requestMock1, $requestMock2, $responseMock);
        $this->apiMock->expects($this->once())
            ->method('callManageRecurringPaymentsProfileStatus')
            ->with($requestMock1);
        $this->objectCopyServiceMock->expects($this->once())
            ->method('copyFieldsetToTarget')
            ->with(
                'aw_sarp_convert_profile',
                'from_api_paypal_response_while_get',
                $apiResponseMock,
                $responseMock
            )
            ->willReturn($responseMock);
        $this->apiMock->expects($this->once())
            ->method('callGetRecurringPaymentsProfileDetails')
            ->with($requestMock2)
            ->willReturn($apiResponseMock);
        $responseMock->expects($this->once())
            ->method('getData')
            ->willReturn(['status' => $expectedStatus]);

        $this->assertEquals($expectedStatus, $this->engine->changeStatus($referenceId, $action));
    }
}
