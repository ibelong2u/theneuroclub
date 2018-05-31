<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet;

use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface;
use Aheadworks\Sarp\Model\Profile\Source\Action;
use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataResolver;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class Engine
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Engine implements EngineInterface
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var DataResolver
     */
    private $engineDataResolver;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param Api $api
     * @param DataResolver $engineDataResolver
     * @param DataObjectFactory $dataObjectFactory
     * @param Copy $objectCopyService
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        Api $api,
        DataResolver $engineDataResolver,
        DataObjectFactory $dataObjectFactory,
        Copy $objectCopyService,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->api = $api;
        $this->engineDataResolver = $engineDataResolver;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->objectCopyService = $objectCopyService;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function submitProfile(
        ProfileInterface $profile,
        $additionalParams = [],
        SubscriptionsCartPaymentInterface $paymentInformation = null
    ) {
        $referenceId = $this->performCreateSubscriptionRequest($profile, $paymentInformation);
        $status = $this->performGetSubscriptionStatusRequest($referenceId);
        return $this->importData(
            $profile,
            $this->dataObjectFactory->create(
                [
                    'profile_id' => $referenceId,
                    'profile_status' => $status
                ]
            ),
            'from_api_authorizenet_response_while_create'
        );
    }

    /**
     * Perform create subscription request
     *
     * @param ProfileInterface $profile
     * @param SubscriptionsCartPaymentInterface $paymentInformation
     * @return int
     */
    private function performCreateSubscriptionRequest(
        ProfileInterface $profile,
        SubscriptionsCartPaymentInterface $paymentInformation
    ) {
        $request = $this->dataObjectFactory->create();
        $this->exportProfileData($profile, $request, 'from_profile_while_create');
        $this->exportPaymentData($paymentInformation, $request);
        $response = $this->api->callARBCreateSubscriptionRequest($request);
        return $response->getProfileId();
    }

    /**
     * Perform get subscription status request
     *
     * @param int $referenceId
     * @return int
     */
    private function performGetSubscriptionStatusRequest($referenceId)
    {
        $request = $this->dataObjectFactory->create(['profile_id' => $referenceId]);
        $response = $this->api->callARBGetSubscriptionStatusRequest($request);
        return $response->getProfileStatus();
    }

    /**
     * {@inheritdoc}
     */
    public function updateProfile(ProfileInterface $profile)
    {
        $request = $this->dataObjectFactory->create();
        try {
            $response = $this->api->callARBUpdateSubscriptionRequest(
                $this->exportProfileData($profile, $request, 'from_profile_while_update')
            );
            return $this->importData($profile, $response, 'from_api_authorizenet_response_while_update');
        } catch (\Exception $e) {
            return $profile;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileData($referenceId)
    {
        $request = $this->dataObjectFactory->create(['profile_id' => $referenceId]);
        $response = $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_profile',
            'from_api_authorizenet_response_while_get',
            $this->api->callARBGetSubscriptionRequest($request),
            $this->dataObjectFactory->create()
        );
        return $response->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function changeStatus($referenceId, $action, $additionalData = [])
    {
        if ($action == Action::CANCEL) {
            $request = $this->dataObjectFactory->create(['profile_id' => $referenceId]);
            $this->api->callARBCancelSubscriptionRequest($request);
            $profileData = $this->getProfileData($referenceId);
            return $profileData['status'];
        }
        return null;
    }

    /**
     * Export profile data to request
     *
     * @param ProfileInterface $profile
     * @param DataObject $request
     * @param string $aspect
     * @return DataObject
     */
    private function exportProfileData($profile, $request, $aspect)
    {
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_api_authorizenet_request',
            $aspect,
            $profile,
            $request
        );
        $request->setAmount(
            $request->getAmount() + $request->getShippingAmount() + $request->getTaxAmount()
        );
        if ($profile->getIsTrialPeriodEnabled() && $profile->getTrialSubtotal() > 0) {
            $this->objectCopyService->copyFieldsetToTarget(
                'aw_sarp_convert_api_authorizenet_request',
                $aspect . '_trial',
                $profile,
                $request
            );
            $request->setTrialAmount(
                $request->getTrialAmount() + $request->getTrialShippingAmount() + $request->getTrialTaxAmount()
            );
        }
        if ($request->getTotalBillingCycles() > 0) {
            $request->setTotalBillingCycles(
                $request->getTotalBillingCycles() + $request->getTrialTotalBillingCycles()
            );
        }

        $request->setProfileDescription(
            $this->engineDataResolver->getProfileDescription($profile)
        );

        foreach ($profile->getAddresses() as $address) {
            $addressType = $address->getAddressType();
            $addressData = $this->dataObjectProcessor->buildOutputDataArray(
                $address,
                ProfileAddressInterface::class
            );
            if (!$profile->getIsCartVirtual() && $addressType == Address::TYPE_SHIPPING) {
                $request->setShippingAddress($addressData);
            } elseif ($addressType == Address::TYPE_BILLING) {
                $request->setBillingAddress($addressData);
            }
        }

        return $request;
    }

    /**
     * Export profile data to request
     *
     * @param SubscriptionsCartPaymentInterface $paymentInformation
     * @param DataObject $request
     * @return DataObject
     */
    private function exportPaymentData($paymentInformation, $request)
    {
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_api_authorizenet_request',
            'from_payment_info_while_create',
            $paymentInformation->getPaymentData(),
            $request
        );
        $request->setCcExpDate(
            $this->engineDataResolver->getCcExpirationDate($request->getCcExpMonth(), $request->getCcExpYear())
        );
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
