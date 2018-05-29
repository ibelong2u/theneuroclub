<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal;

use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataResolver;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp as ApiNvp;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class Engine
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Engine implements EngineInterface
{
    /**
     * @var ApiNvp
     */
    private $api;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var DataResolver
     */
    private $engineDataResolver;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param ApiNvp $api
     * @param DataObjectFactory $dataObjectFactory
     * @param DataResolver $engineDataResolver
     * @param Copy $objectCopyService
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        ApiNvp $api,
        DataObjectFactory $dataObjectFactory,
        DataResolver $engineDataResolver,
        Copy $objectCopyService,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->api = $api;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->engineDataResolver = $engineDataResolver;
        $this->objectCopyService = $objectCopyService;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function submitProfile(
        ProfileInterface $profile,
        $additionalParams = [],
        SubscriptionsCartPaymentInterface $paymentInformation = null
    ) {
        /** @var DataObject $request */
        $request = $this->dataObjectFactory->create(['token' => $additionalParams['token']]);
        $response = $this->api->callCreateRecurringPaymentsProfile(
            $this->exportData($profile, $request, 'from_profile_while_create')
        );
        return $this->importData($profile, $response, 'from_api_paypal_response_while_create');
    }

    /**
     * {@inheritdoc}
     */
    public function updateProfile(ProfileInterface $profile)
    {
        /** @var DataObject $request */
        $request = $this->dataObjectFactory->create();
        try {
            $response = $this->api->callUpdateRecurringPaymentsProfile(
                $this->exportData($profile, $request, 'from_profile_while_update')
            );
            return $this->importData($profile, $response, 'from_api_paypal_response_while_update');
        } catch (\Exception $e) {
            return $profile;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileData($referenceId)
    {
        /** @var DataObject $request */
        $request = $this->dataObjectFactory->create(['profile_id' => $referenceId]);
        $response = $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_profile',
            'from_api_paypal_response_while_get',
            $this->api->callGetRecurringPaymentsProfileDetails($request),
            $this->dataObjectFactory->create()
        );
        return $response->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function changeStatus($referenceId, $action, $additionalData = [])
    {
        $request = $this->dataObjectFactory->create(
            [
                'profile_id' => $referenceId,
                'action' => $action
            ]
        );
        if (isset($additionalData['note'])) {
            $request->setNote($additionalData['note']);
        }
        $this->api->callManageRecurringPaymentsProfileStatus($request);
        $profileData = $this->getProfileData($referenceId);
        return $profileData['status'];
    }

    /**
     * Export data to request
     *
     * @param ProfileInterface $profile
     * @param DataObject $request
     * @param string $aspect
     * @return DataObject
     */
    private function exportData($profile, $request, $aspect)
    {
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_api_paypal_request',
            $aspect,
            $profile,
            $request
        );
        if ($profile->getIsTrialPeriodEnabled() && $profile->getTrialSubtotal() > 0) {
            $this->objectCopyService->copyFieldsetToTarget(
                'aw_sarp_convert_api_paypal_request',
                $aspect . '_trial',
                $profile,
                $request
            );
        }
        if ($profile->getIsInitialFeeEnabled()) {
            $this->objectCopyService->copyFieldsetToTarget(
                'aw_sarp_convert_api_paypal_request',
                $aspect . '_initial',
                $profile,
                $request
            );
        }

        $request->setProfileDescription(
            $this->engineDataResolver->getProfileDescription($profile)
        );

        foreach ($profile->getAddresses() as $address) {
            $addressData = $this->dataObjectProcessor->buildOutputDataArray(
                $address,
                ProfileAddressInterface::class
            );
            if (!$profile->getIsCartVirtual() && $address->getAddressType() == Address::TYPE_SHIPPING) {
                $request->setShippingAddress($addressData);
            } else {
                $request->setBillingAddress($addressData);
            }
        }

        return $request;
    }

    /**
     * Import data from response
     *
     * @param ProfileInterface $profile
     * @param DataObject $response
     * @param string $aspect
     * @return ProfileInterface
     */
    private function importData($profile, $response, $aspect)
    {
        $this->objectCopyService->copyFieldsetToTarget('aw_sarp_convert_profile', $aspect, $response, $profile);
        return $profile;
    }
}
