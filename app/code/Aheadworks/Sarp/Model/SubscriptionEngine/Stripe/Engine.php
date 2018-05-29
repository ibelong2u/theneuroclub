<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe;

use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface;
use Aheadworks\Sarp\Api\SubscriptionPlanRepositoryInterface;
use Aheadworks\Sarp\Model\Profile\Source\Action;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataResolver;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Resolver\FullName;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Resolver\Region;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class Engine
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Engine implements EngineInterface
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $planRepository;

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
     * @var FullName
     */
    private $fullNameResolver;

    /**
     * @var Region
     */
    private $regionResolver;

    /**
     * @param Api $api
     * @param SubscriptionPlanRepositoryInterface $planRepository
     * @param DataResolver $engineDataResolver
     * @param DataObjectFactory $dataObjectFactory
     * @param Copy $objectCopyService
     * @param DataObjectProcessor $dataObjectProcessor
     * @param FullName $fullNameResolver
     * @param Region $regionResolver
     */
    public function __construct(
        Api $api,
        SubscriptionPlanRepositoryInterface $planRepository,
        DataResolver $engineDataResolver,
        DataObjectFactory $dataObjectFactory,
        Copy $objectCopyService,
        DataObjectProcessor $dataObjectProcessor,
        FullName $fullNameResolver,
        Region $regionResolver
    ) {
        $this->api = $api;
        $this->planRepository = $planRepository;
        $this->engineDataResolver = $engineDataResolver;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->objectCopyService = $objectCopyService;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->fullNameResolver = $fullNameResolver;
        $this->regionResolver = $regionResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function submitProfile(
        ProfileInterface $profile,
        $additionalParams = [],
        SubscriptionsCartPaymentInterface $paymentInformation = null
    ) {
        $requestParams = $this->dataObjectFactory->create(
            [
                'plan' => $this->performCreatePlanRequest($profile),
                'customer' => $this->performCreateCustomerRequest($profile, $paymentInformation)
            ]
        );
        $response = $this->api->requestCreateSubscription($requestParams);
        return $this->importData($profile, $response, 'from_api_stripe_response_while_create');
    }

    /**
     * Perform create plan request
     *
     * @param ProfileInterface $profile
     * @return int
     */
    private function performCreatePlanRequest(ProfileInterface $profile)
    {
        $requestParams = $this->dataObjectFactory->create();

        $plan = $this->planRepository->get($profile->getSubscriptionPlanId());
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_api_stripe_request',
            'from_subscription_plan_while_create_plan',
            $plan,
            $requestParams
        );
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_api_stripe_request',
            'from_profile_while_create_plan',
            $profile,
            $requestParams
        );
        $requestParams->setId(uniqid($plan->getSubscriptionPlanId()));

        $response = $this->api->requestCreatePlan($requestParams);
        return $response->getId();
    }

    /**
     * Perform create customer request
     *
     * @param ProfileInterface $profile
     * @param SubscriptionsCartPaymentInterface $paymentInformation
     * @return int
     */
    private function performCreateCustomerRequest(
        ProfileInterface $profile,
        SubscriptionsCartPaymentInterface $paymentInformation
    ) {
        $requestParams = $this->dataObjectFactory->create();

        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_api_stripe_request',
            'from_profile_while_create_customer',
            $profile,
            $requestParams
        );
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_api_stripe_request',
            'from_payment_info_while_create_customer',
            $paymentInformation->getPaymentData(),
            $requestParams
        );
        if (!$profile->getIsCartVirtual()) {
            $this->exportShippingAddress($profile, $requestParams);
        }

        $response = $this->api->requestCreateCustomer($requestParams);
        return $response->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function updateProfile(ProfileInterface $profile)
    {
        $subscriptionData = $this->api->requestRetrieveSubscription($profile->getReferenceId());
        $this->performUpdatePlanRequest($subscriptionData->getPlanId(), $profile);
        $this->performUpdateCustomerRequest($subscriptionData->getCustomerId(), $profile);
    }

    /**
     * Perform update plan request
     *
     * @param int $planId
     * @param ProfileInterface $profile
     * @return void
     */
    private function performUpdatePlanRequest($planId, ProfileInterface $profile)
    {
        $requestParams = $this->dataObjectFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_api_stripe_request',
            'from_profile_while_update_plan',
            $profile,
            $requestParams
        );
        $this->api->requestUpdatePlan($planId, $requestParams);
    }

    /**
     * Perform update customer request
     *
     * @param int $customerId
     * @param ProfileInterface $profile
     * @return void
     */
    private function performUpdateCustomerRequest($customerId, ProfileInterface $profile)
    {
        $requestParams = $this->exportShippingAddress($profile, $this->dataObjectFactory->create());
        $this->api->requestUpdateCustomer($customerId, $requestParams);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileData($referenceId)
    {
        $response = $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_profile',
            'from_api_stripe_response_while_get',
            $this->api->requestRetrieveSubscription($referenceId),
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
            $response = $this->api->requestCancelSubscription($referenceId);
            return $response->getProfileStatus();
        }
        return null;
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

    /**
     * Export shipping address to request parameters
     *
     * @param ProfileInterface $profile
     * @param DataObject $requestParams
     * @return DataObject
     */
    private function exportShippingAddress($profile, $requestParams)
    {
        foreach ($profile->getAddresses() as $address) {
            if ($address->getAddressType() == Address::TYPE_SHIPPING) {
                $addressData = $this->dataObjectProcessor->buildOutputDataArray(
                    $address,
                    ProfileAddressInterface::class
                );
                $requestParams->setShippingAddress(
                    array_merge(
                        $addressData,
                        [
                            'customer_name' => $this->fullNameResolver->getFullName($address),
                            'state' => $this->regionResolver->getRegion(
                                $address->getRegionId(),
                                $address->getRegion(),
                                $address->getCountryId()
                            )
                        ]
                    )
                );
            }
        }
        return $requestParams;
    }
}
