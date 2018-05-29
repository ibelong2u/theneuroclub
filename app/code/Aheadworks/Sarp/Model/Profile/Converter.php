<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Profile;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfileInterfaceFactory;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\SubscriptionPlanRepositoryInterface;
use Aheadworks\Sarp\Model\PaymentMethodList;
use Aheadworks\Sarp\Model\Profile\Address\Converter as AddressConverter;
use Aheadworks\Sarp\Model\Profile\Item\Converter as ItemConverter;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address as CartAddress;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Resolver\FullName as FullNameResolver;
use Magento\Framework\DataObject\Copy;

/**
 * Class Converter
 * @package Aheadworks\Sarp\Model\Profile
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Converter
{
    /**
     * @var ProfileInterfaceFactory
     */
    private $profileFactory;

    /**
     * @var AddressConverter
     */
    private $addressConverter;

    /**
     * @var FullNameResolver
     */
    private $fullNameResolver;

    /**
     * @var ItemConverter
     */
    private $itemConverter;

    /**
     * @var StartDateResolver
     */
    private $startDateResolver;

    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var PaymentMethodList
     */
    private $paymentMethodList;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param ProfileInterfaceFactory $profileFactory
     * @param AddressConverter $addressConverter
     * @param FullNameResolver $fullNameResolver
     * @param ItemConverter $itemConverter
     * @param StartDateResolver $startDateResolver
     * @param SubscriptionPlanRepositoryInterface $planRepository
     * @param PaymentMethodList $paymentMethodList
     * @param Copy $objectCopyService
     */
    public function __construct(
        ProfileInterfaceFactory $profileFactory,
        AddressConverter $addressConverter,
        FullNameResolver $fullNameResolver,
        ItemConverter $itemConverter,
        StartDateResolver $startDateResolver,
        SubscriptionPlanRepositoryInterface $planRepository,
        PaymentMethodList $paymentMethodList,
        Copy $objectCopyService
    ) {
        $this->profileFactory = $profileFactory;
        $this->addressConverter = $addressConverter;
        $this->fullNameResolver = $fullNameResolver;
        $this->itemConverter = $itemConverter;
        $this->startDateResolver = $startDateResolver;
        $this->planRepository = $planRepository;
        $this->paymentMethodList = $paymentMethodList;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Convert from subscription cart
     *
     * @param SubscriptionsCartInterface $cart
     * @return ProfileInterface
     */
    public function fromSubscriptionCart(SubscriptionsCartInterface $cart)
    {
        /** @var ProfileInterface $profile */
        $profile = $this->profileFactory->create();
        $this->objectCopyService->copyFieldsetToTarget('aw_sarp_convert_profile', 'from_cart', $cart, $profile);
        $profileItems = [];
        foreach ($cart->getItems() as $cartItem) {
            $profileItems[] = $this->itemConverter->fromCartItem($cartItem, $cart);
        }
        $profile
            ->setItems($profileItems)
            ->setInnerItems($profileItems);

        $profileAddresses = [];
        foreach ($cart->getAddresses() as $cartAddress) {
            $profileAddresses[] = $this->addressConverter->fromCartAddress($cartAddress);

            $resolveFullNameAddressType = $cart->getIsVirtual()
                ? CartAddress::TYPE_BILLING
                : CartAddress::TYPE_SHIPPING;
            if ($cartAddress->getAddressType() == $resolveFullNameAddressType) {
                $profile->setCustomerFullname($this->fullNameResolver->getFullName($cartAddress));
            }
        }
        $profile->setAddresses($profileAddresses);

        $plan = $this->planRepository->get($cart->getSubscriptionPlanId());
        $this->objectCopyService->copyFieldsetToTarget('aw_sarp_convert_profile', 'from_plan', $plan, $profile);
        if (!$profile->getStartDate()) {
            $profile->setStartDate(
                $this->startDateResolver->getStartDate(
                    $plan->getStartDateType(),
                    $plan->getStartDateDayOfMonth()
                )
            );
        }
        $paymentMethod = $this->paymentMethodList->getMethod($plan->getEngineCode(), $cart->getPaymentMethodCode());
        $profile->setPaymentMethodTitle($paymentMethod->getTitle());

        return $profile;
    }
}
