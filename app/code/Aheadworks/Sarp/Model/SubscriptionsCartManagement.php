<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model;

use Aheadworks\Sarp\Api\Data\ShippingInformationInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterfaceFactory;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartItemRepositoryInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartManagementInterface;
use Aheadworks\Sarp\Api\SubscriptionPlanRepositoryInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Model\Logger\LoggerInterface;
use Aheadworks\Sarp\Model\Profile\Converter as ProfileConverter;
use Aheadworks\Sarp\Model\Session as SarpSession;
use Aheadworks\Sarp\Model\SubscriptionEngine\EnginePool;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Aheadworks\Sarp\Model\SubscriptionsCart\CheckoutValidator;
use Aheadworks\Sarp\Model\SubscriptionsCart\ItemsAddToCartValidator;
use Aheadworks\Sarp\Model\SubscriptionsCart\ItemsComparator;
use Aheadworks\Sarp\Model\SubscriptionsCart\ItemsProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SubscriptionsCartManagement
 * @package Aheadworks\Sarp\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriptionsCartManagement implements SubscriptionsCartManagementInterface
{
    /**
     * @var SubscriptionsCartRepositoryInterface
     */
    private $subscriptionsCartRepository;

    /**
     * @var SubscriptionsCartItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var SubscriptionsCartAddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var ItemsComparator
     */
    private $itemsComparator;

    /**
     * @var ItemsProcessor
     */
    private $itemsProcessor;

    /**
     * @var ItemsAddToCartValidator
     */
    private $itemsValidator;

    /**
     * @var CheckoutValidator
     */
    private $checkoutValidator;

    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var EnginePool
     */
    private $enginePool;

    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ProfileConverter
     */
    private $profileConverter;

    /**
     * @var SarpSession
     */
    private $sarpSession;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SubscriptionsCartRepositoryInterface $subscriptionsCartRepository
     * @param SubscriptionsCartItemRepositoryInterface $itemRepository
     * @param SubscriptionsCartAddressInterfaceFactory $addressFactory
     * @param ItemsComparator $itemsComparator
     * @param ItemsProcessor $itemsProcessor
     * @param ItemsAddToCartValidator $itemsValidator
     * @param CheckoutValidator $checkoutValidator
     * @param SubscriptionPlanRepositoryInterface $planRepository
     * @param EnginePool $enginePool
     * @param EngineMetadataPool $engineMetadataPool
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileConverter $profileConverter
     * @param Session $sarpSession
     * @param DataObjectHelper $dataObjectHelper
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        SubscriptionsCartRepositoryInterface $subscriptionsCartRepository,
        SubscriptionsCartItemRepositoryInterface $itemRepository,
        SubscriptionsCartAddressInterfaceFactory $addressFactory,
        ItemsComparator $itemsComparator,
        ItemsProcessor $itemsProcessor,
        ItemsAddToCartValidator $itemsValidator,
        CheckoutValidator $checkoutValidator,
        SubscriptionPlanRepositoryInterface $planRepository,
        EnginePool $enginePool,
        EngineMetadataPool $engineMetadataPool,
        ProfileRepositoryInterface $profileRepository,
        ProfileConverter $profileConverter,
        SarpSession $sarpSession,
        DataObjectHelper $dataObjectHelper,
        LoggerInterface $logger
    ) {
        $this->subscriptionsCartRepository = $subscriptionsCartRepository;
        $this->itemRepository = $itemRepository;
        $this->addressFactory = $addressFactory;
        $this->itemsComparator = $itemsComparator;
        $this->itemsProcessor = $itemsProcessor;
        $this->itemsValidator = $itemsValidator;
        $this->checkoutValidator = $checkoutValidator;
        $this->planRepository = $planRepository;
        $this->enginePool = $enginePool;
        $this->engineMetadataPool = $engineMetadataPool;
        $this->profileRepository = $profileRepository;
        $this->profileConverter = $profileConverter;
        $this->sarpSession = $sarpSession;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function add(SubscriptionsCartInterface $cart, SubscriptionsCartItemInterface $cartItem)
    {
        if (!$this->itemsValidator->isValid($cartItem)) {
            throw new LocalizedException(__('Item cannot be added to subscription cart.'));
        }

        $cartItemsToAdd = $this->itemsProcessor->processBeforeAdd($cart, $cartItem);

        $cartItems = $cart->getInnerItems();
        $cartItemWithProduct = $this->findSameItem($cartItem, $cartItems);
        if (!$cartItemWithProduct) {
            $cartItems = array_merge($cartItems, $cartItemsToAdd);
        } else {
            $cartItemWithProduct->setQty($cartItemWithProduct->getQty() + $cartItemsToAdd[0]->getQty());
        }

        $cart
            ->setInnerItems($cartItems)
            ->setSubscriptionPlanId(null);
        if (!$cart->getAddresses()) {
            $this->initAddresses($cart);
        }

        $this->subscriptionsCartRepository->save($cart);
        return $cartItem;
    }

    /**
     * {@inheritdoc}
     */
    public function selectSubscriptionPlan($cartId, $planId = null)
    {
        $cart = $this->subscriptionsCartRepository->getActive($cartId);
        $cart->setSubscriptionPlanId($planId);
        return $this->subscriptionsCartRepository->save($cart);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(SubscriptionsCartInterface $cart1, SubscriptionsCartInterface $cart2)
    {
        $cartItems = $cart1->getItems();
        /** @var SubscriptionsCartItemInterface $cartItem */
        foreach ($cart2->getItems() as $cartItem) {
            $existingItem = $this->findSameItem($cartItem, $cartItems);
            if (!$existingItem) {
                $cartItems[] = $cartItem;
            } else {
                $existingItem->setQty($existingItem->getQty() + $cartItem->getQty());
            }
        }

        $cart1
            ->setItems($cartItems)
            ->setSubscriptionPlanId(null);
        if (!$cart1->getAddresses()) {
            $this->initAddresses($cart1);
        }

        return $this->subscriptionsCartRepository->save($cart1);
    }

    /**
     * {@inheritdoc}
     */
    public function saveShippingInformation($cartId, ShippingInformationInterface $shippingInformation)
    {
        $shippingAddress = $shippingInformation->getShippingAddress();
        $billingAddress = $shippingInformation->getBillingAddress();

        $cart = $this->subscriptionsCartRepository->getActive($cartId);
        $cartItems = $this->itemRepository->getList($cartId);
        if ($cartItems->getTotalCount() == 0) {
            throw new InputException(__('Shipping method is not applicable for empty subscriptions cart.'));
        }

        $shippingAddress = $this->populateAddressesData($cart, $shippingAddress, $billingAddress);
        $shippingAddress
            ->setCartId($cartId)
            ->setShippingMethodCode($shippingInformation->getShippingMethodCode())
            ->setShippingCarrierCode($shippingInformation->getShippingCarrierCode());

        try {
            $this->subscriptionsCartRepository->save($cart);
        } catch (\Exception $e) {
            throw new InputException(__('Unable to save shipping information. Please check input data.'));
        }
        return $cart;
    }

    /**
     * {@inheritdoc}
     */
    public function saveGuestShippingInformation($cartId, $email, ShippingInformationInterface $shippingInformation)
    {
        $shippingAddress = $shippingInformation->getShippingAddress();
        $shippingAddress->setEmail($email);
        $billingAddress = $shippingInformation->getBillingAddress();
        if ($billingAddress) {
            $billingAddress->setEmail($email);
        }

        $cart = $this->subscriptionsCartRepository->getActive($cartId);
        $cart
            ->setCustomerEmail($email)
            ->setCustomerPrefix($shippingAddress->getPrefix())
            ->setCustomerFirstname($shippingAddress->getFirstname())
            ->setCustomerLastname($shippingAddress->getLastname())
            ->setCustomerMiddlename($shippingAddress->getMiddlename())
            ->setCustomerSuffix($shippingAddress->getSuffix())
            ->setCustomerIsGuest(true);

        return $this->saveShippingInformation($cart->getCartId(), $shippingInformation);
    }

    /**
     * {@inheritdoc}
     */
    public function saveBillingAddress($cartId, SubscriptionsCartAddressInterface $billingAddress)
    {
        $cart = $this->subscriptionsCartRepository->getActive($cartId);
        $this->populateAddressesData($cart, null, $billingAddress);

        try {
            $this->subscriptionsCartRepository->save($cart);
        } catch (\Exception $e) {
            throw new InputException(__('Unable to save billing address. Please check input data.'));
        }
        return $cart;
    }

    /**
     * {@inheritdoc}
     */
    public function saveGuestBillingAddress($cartId, $email, SubscriptionsCartAddressInterface $billingAddress)
    {
        $billingAddress->setEmail($email);
        return $this->saveBillingAddress($cartId, $billingAddress);
    }

    /**
     * {@inheritdoc}
     */
    public function savePaymentInformation(
        $cartId,
        SubscriptionsCartPaymentInterface $paymentInformation,
        SubscriptionsCartAddressInterface $billingAddress = null
    ) {
        $cart = $this->subscriptionsCartRepository->getActive($cartId);
        $cart->setPaymentMethodCode($paymentInformation->getMethodCode());

        if ($billingAddress) {
            $this->populateAddressesData($cart, null, $billingAddress);
        }
        try {
            $this->subscriptionsCartRepository->save($cart);
        } catch (\Exception $e) {
            throw new InputException(__('Unable to save payment information. Please check input data.'));
        }
        return $cart;
    }

    /**
     * {@inheritdoc}
     */
    public function savePaymentInformationAndSubmit(
        $cartId,
        SubscriptionsCartPaymentInterface $paymentInformation,
        SubscriptionsCartAddressInterface $billingAddress = null
    ) {
        $this->savePaymentInformation($cartId, $paymentInformation, $billingAddress);
        return $this->submit($cartId, [], $paymentInformation);
    }

    /**
     * {@inheritdoc}
     */
    public function saveGuestPaymentInformation(
        $cartId,
        $email,
        SubscriptionsCartPaymentInterface $paymentInformation,
        SubscriptionsCartAddressInterface $billingAddress = null
    ) {
        $cart = $this->subscriptionsCartRepository->getActive($cartId);
        $cart->setCustomerEmail($email)
            ->setCustomerIsGuest(true);
        if ($billingAddress) {
            $billingAddress->setEmail($email);
            $cart->setCustomerPrefix($billingAddress->getPrefix())
                ->setCustomerFirstname($billingAddress->getFirstname())
                ->setCustomerLastname($billingAddress->getLastname())
                ->setCustomerMiddlename($billingAddress->getMiddlename())
                ->setCustomerSuffix($billingAddress->getSuffix());
        }
        return $this->savePaymentInformation($cartId, $paymentInformation, $billingAddress);
    }

    /**
     * {@inheritdoc}
     */
    public function saveGuestPaymentInformationAndSubmit(
        $cartId,
        $email,
        SubscriptionsCartPaymentInterface $paymentInformation,
        SubscriptionsCartAddressInterface $billingAddress = null
    ) {
        $this->saveGuestPaymentInformation($cartId, $email, $paymentInformation, $billingAddress);
        return $this->submit($cartId, [], $paymentInformation);
    }

    /**
     * {@inheritdoc}
     */
    public function submit(
        $cartId,
        $additionalParams = [],
        SubscriptionsCartPaymentInterface $paymentInformation = null
    ) {
        $cart = $this->subscriptionsCartRepository->getActive($cartId);
        if (!$this->checkoutValidator->isValid($cart)) {
            $messages = $this->checkoutValidator->getMessages();
            throw new LocalizedException(__(array_pop($messages)));
        }

        $plan = $this->planRepository->get($cart->getSubscriptionPlanId());
        $engine = $this->enginePool->getEngine($plan->getEngineCode());
        $profile = $this->profileConverter->fromSubscriptionCart($cart);
        try {
            $profile = $engine->submitProfile($profile, $additionalParams, $paymentInformation);
            $this->profileRepository->save($profile);

            $engineMetadata = $this->engineMetadataPool->getMetadata($plan->getEngineCode());
            if ($engineMetadata->isGateway()) {
                $this->logger->notice($profile, LoggerInterface::ENTRY_TYPE_PROFILE_CREATED_SUCCESSFUL);
            }
        } catch (\Exception $e) {
            $this->logger->error(
                $profile,
                LoggerInterface::ENTRY_TYPE_PROFILE_CREATION_FAILED,
                ['exception' => $e]
            );
            throw $e;
        }

        $this->sarpSession
            ->setLastSuccessCartId($cartId)
            ->setLastProfileId($profile->getProfileId());

        $cart->setIsActive(false);
        return $this->subscriptionsCartRepository->save($cart, false);
    }

    /**
     * Init addresses entities for cart
     *
     * @param SubscriptionsCartInterface $cart
     * @return void
     */
    private function initAddresses(SubscriptionsCartInterface $cart)
    {
        $cart->setAddresses(
            [
                $this->addressFactory->create()
                    ->setAddressType(Address::TYPE_BILLING),
                $this->addressFactory->create()
                    ->setAddressType(Address::TYPE_SHIPPING)
            ]
        );
    }

    /**
     * Populate cart with addresses data
     *
     * @param SubscriptionsCartInterface $cart
     * @param SubscriptionsCartAddressInterface|null $shippingAddress
     * @param SubscriptionsCartAddressInterface|null $billingAddress
     * @return SubscriptionsCartAddressInterface|null
     */
    private function populateAddressesData(
        SubscriptionsCartInterface $cart,
        SubscriptionsCartAddressInterface $shippingAddress = null,
        SubscriptionsCartAddressInterface $billingAddress = null
    ) {
        $addressesToUpdate = [];
        foreach ($cart->getAddresses() as $cartAddress) {
            $updatedAddress = $cartAddress->getAddressType() == Address::TYPE_SHIPPING && $shippingAddress
                ? $shippingAddress
                : ($cartAddress->getAddressType() == Address::TYPE_BILLING && $billingAddress
                    ? $billingAddress
                    : null
                );
            if ($updatedAddress) {
                /** @var SubscriptionsCartAddressInterface $address */
                $address = $this->addressFactory->create();

                $this->dataObjectHelper->mergeDataObjects(
                    SubscriptionsCartAddressInterface::class,
                    $address,
                    $updatedAddress
                );
                $address
                    ->setAddressId($cartAddress->getAddressId())
                    ->setCartId($cartAddress->getCartId())
                    ->setAddressType($cartAddress->getAddressType());

                if ($updatedAddress->getAddressType() == Address::TYPE_SHIPPING) {
                    $shippingAddress = $address;
                }
                $addressesToUpdate[] = $address;
            } else {
                $addressesToUpdate[] = $cartAddress;
            }
        }
        $cart->setAddresses($addressesToUpdate);

        return $shippingAddress;
    }

    /**
     * Find the same item (that represents the same product with equals options) in the items list
     *
     * @param SubscriptionsCartItemInterface $cartItem
     * @param SubscriptionsCartItemInterface[] $itemsList
     * @return SubscriptionsCartItemInterface|bool
     */
    private function findSameItem(SubscriptionsCartItemInterface $cartItem, array $itemsList)
    {
        foreach ($itemsList as $item) {
            if (!$item->getParentItemId() && $this->itemsComparator->isEquals($cartItem, $item)) {
                return $item;
            }
        }
        return false;
    }
}
