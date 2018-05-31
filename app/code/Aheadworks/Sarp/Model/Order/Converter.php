<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Order;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;
use Aheadworks\Sarp\Model\Order\Address\Converter as AddressConverter;
use Aheadworks\Sarp\Model\Order\Item\Converter as ItemConverter;
use Aheadworks\Sarp\Model\Order\ShippingAssignment\Initializer as ShippingAssignmentInitializer;
use Aheadworks\Sarp\Model\Order\Payment\Initializer as PaymentInitializer;
use Aheadworks\Sarp\Model\Profile\PaymentInfo;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterfaceFactory;
use Magento\Sales\Api\Data\ShippingAssignmentInterface;
use Magento\Sales\Api\Data\ShippingAssignmentInterfaceFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DataObject\Copy;

/**
 * Class Converter
 * @package Aheadworks\Sarp\Model\Order
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Converter
{
    /**
     * @var OrderInterfaceFactory
     */
    private $orderFactory;

    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    /**
     * @var AddressConverter
     */
    private $addressConverter;

    /**
     * @var ItemConverter
     */
    private $itemConverter;

    /**
     * @var ShippingAssignmentInterfaceFactory
     */
    private $shippingAssignmentFactory;

    /**
     * @var ShippingAssignmentInitializer
     */
    private $shippingAssignmentInitializer;

    /**
     * @var OrderPaymentInterfaceFactory
     */
    private $paymentFactory;

    /**
     * @var PaymentInitializer
     */
    private $paymentInitializer;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param OrderInterfaceFactory $orderFactory
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     * @param AddressConverter $addressConverter
     * @param ItemConverter $itemConverter
     * @param ShippingAssignmentInterfaceFactory $shippingAssignmentFactory
     * @param ShippingAssignmentInitializer $shippingAssignmentInitializer
     * @param OrderPaymentInterfaceFactory $paymentFactory
     * @param PaymentInitializer $paymentInitializer
     * @param Copy $objectCopyService
     */
    public function __construct(
        OrderInterfaceFactory $orderFactory,
        ExtensionAttributesFactory $extensionAttributesFactory,
        AddressConverter $addressConverter,
        ItemConverter $itemConverter,
        ShippingAssignmentInterfaceFactory $shippingAssignmentFactory,
        ShippingAssignmentInitializer $shippingAssignmentInitializer,
        OrderPaymentInterfaceFactory $paymentFactory,
        PaymentInitializer $paymentInitializer,
        Copy $objectCopyService
    ) {
        $this->orderFactory = $orderFactory;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->addressConverter = $addressConverter;
        $this->itemConverter = $itemConverter;
        $this->shippingAssignmentFactory = $shippingAssignmentFactory;
        $this->shippingAssignmentInitializer = $shippingAssignmentInitializer;
        $this->paymentFactory = $paymentFactory;
        $this->paymentInitializer = $paymentInitializer;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Convert from subscription profile
     *
     * @param ProfileInterface $profile
     * @param ProfilePaymentInfoInterface $paymentInfo
     * @return OrderInterface
     */
    public function fromProfile(ProfileInterface $profile, ProfilePaymentInfoInterface $paymentInfo)
    {
        /** @var OrderInterface $order */
        $order = $this->orderFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_profile',
            'to_order',
            $profile,
            $order
        );
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_profile_payment_info',
            'to_order',
            $paymentInfo,
            $order
        );
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_profile_payment_info',
            'to_order_paid',
            $paymentInfo,
            $order
        );

        $this->importAddresses($profile, $order);

        /** @var OrderPaymentInterface $payment */
        $payment = $this->paymentFactory->create();
        $this->paymentInitializer->init($payment, $profile, $paymentInfo, $order);
        $order->setPayment($payment);

        $orderItems = [];
        if ($paymentInfo->getPaymentType() == PaymentInfo::PAYMENT_TYPE_INITIAL) {
            $orderItems[] = $this->itemConverter->fromPaymentInfoAsInitial($paymentInfo, $profile->getStoreId());
        } else {
            foreach ($profile->getItems() as $item) {
                $orderItems = array_merge(
                    $orderItems,
                    $this->itemConverter->fromProfileItem($item, $paymentInfo, $profile)
                );
            }
        }
        $order->setItems($orderItems);

        return $order;
    }

    /**
     * Import addresses from profile to order
     *
     * @param ProfileInterface $profile
     * @param OrderInterface $order
     * @return void
     */
    private function importAddresses(ProfileInterface $profile, OrderInterface $order)
    {
        foreach ($profile->getAddresses() as $profileAddress) {
            $address = $this->addressConverter->fromProfileAddress($profileAddress);
            if ($profileAddress->getAddressType() == Address::TYPE_BILLING) {
                $order->setBillingAddress($address);
            } elseif (!$profile->getIsCartVirtual()) {
                $order->setShippingAddress($address);
                /** @var ShippingAssignmentInterface $shippingAssignment */
                $shippingAssignment = $this->shippingAssignmentFactory->create();
                $this->shippingAssignmentInitializer->init($shippingAssignment, $profile, $address);

                /** @var OrderExtensionInterface $orderExtensionAttributes */
                $orderExtensionAttributes = $this->extensionAttributesFactory->create(OrderInterface::class);
                $orderExtensionAttributes->setShippingAssignments([$shippingAssignment]);
                $order->setExtensionAttributes($orderExtensionAttributes);
            }
        }
    }
}
