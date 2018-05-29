<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;
use Aheadworks\Sarp\Api\ProfileManagementInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\Logger\LoggerInterface;
use Aheadworks\Sarp\Model\Order\Converter;
use Aheadworks\Sarp\Model\Order\InventoryManagement;
use Aheadworks\Sarp\Model\SubscriptionEngine\EnginePool;
use Aheadworks\Sarp\Model\SubscriptionEngine\Exception\OperationIsNotSupportedException;
use Aheadworks\Sarp\Model\SubscriptionEngine\ProfileActionValidator;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class ProfileManagement
 * @package Aheadworks\Sarp\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProfileManagement implements ProfileManagementInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Converter
     */
    private $orderConverter;

    /**
     * @var InventoryManagement
     */
    private $inventoryManagement;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var EnginePool
     */
    private $enginePool;

    /**
     * @var ProfileActionValidator
     */
    private $profileActionValidator;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param Converter $orderConverter
     * @param InventoryManagement $inventoryManagement
     * @param ProfileRepositoryInterface $profileRepository
     * @param EnginePool $enginePool
     * @param ProfileActionValidator $profileActionValidator
     * @param DataObjectHelper $dataObjectHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Converter $orderConverter,
        InventoryManagement $inventoryManagement,
        ProfileRepositoryInterface $profileRepository,
        EnginePool $enginePool,
        ProfileActionValidator $profileActionValidator,
        DataObjectHelper $dataObjectHelper,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderConverter = $orderConverter;
        $this->inventoryManagement = $inventoryManagement;
        $this->profileRepository = $profileRepository;
        $this->enginePool = $enginePool;
        $this->profileActionValidator = $profileActionValidator;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrder(ProfileInterface $profile, ProfilePaymentInfoInterface $paymentInfo)
    {
        $order = $this->orderConverter->fromProfile($profile, $paymentInfo);
        try {
            $this->inventoryManagement->subtract($profile);
            $order = $this->place($order);
            $invoice = $this->createInvoice($order, $paymentInfo);
            $this->logger->notice(
                $profile,
                LoggerInterface::ENTRY_TYPE_PAYMENT_PAID,
                [
                    'order' => $order,
                    'invoice' => $invoice
                ]
            );
        } catch (\Exception $e) {
            $this->inventoryManagement->revert($profile);
            throw $e;
        }

        $profile
            ->setLastOrderId($order->getEntityId())
            ->setLastOrderDate($order->getCreatedAt());
        $this->profileRepository->save($profile, $order->getEntityId());

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function changeStatusAction($profileId, $action)
    {
        $profile = $this->profileRepository->get($profileId);
        if (!$this->profileActionValidator->isValidForAction($profile, $action)) {
            throw new OperationIsNotSupportedException($this->profileActionValidator->getMessage());
        }
        $engine = $this->enginePool->getEngine($profile->getEngineCode());
        $status = $engine->changeStatus($profile->getReferenceId(), $action);
        $profile->setStatus($status);
        $this->profileRepository->save($profile);
        $this->logger->notice($profile, LoggerInterface::ENTRY_TYPE_PROFILE_STATUS_CHANGED);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshProfileData($profileId)
    {
        $profile = $this->profileRepository->get($profileId);
        $engine = $this->enginePool->getEngine($profile->getEngineCode());
        $status = $profile->getStatus();
        $this->dataObjectHelper->populateWithArray(
            $profile,
            $engine->getProfileData($profile->getReferenceId()),
            ProfileInterface::class
        );
        if ($status != $profile->getStatus()) {
            $this->logger->notice($profile, LoggerInterface::ENTRY_TYPE_PROFILE_STATUS_CHANGED);
        }
        return $this->profileRepository->save($profile);
    }

    /**
     * {@inheritdoc}
     */
    public function updateProfileAddress($profileId, $addressType, ProfileAddressInterface $address)
    {
        $profile = $this->profileRepository->get($profileId);
        $address->setAddressType($addressType);
        $this->populateAddressData($profile, $address);
        $engine = $this->enginePool->getEngine($profile->getEngineCode());
        $engine->updateProfile($profile);
        $this->profileRepository->save($profile);
        return true;
    }

    /**
     * Populate profile with address data
     *
     * @param ProfileInterface $profile
     * @param ProfileAddressInterface $address
     * @return ProfileInterface
     */
    private function populateAddressData(
        ProfileInterface $profile,
        ProfileAddressInterface $address
    ) {
        $addressesToUpdate = [];
        foreach ($profile->getAddresses() as $profileAddress) {
            if ($profileAddress->getAddressType() == $address->getAddressType()) {
                $address->setEmail($profileAddress->getEmail());
                $this->dataObjectHelper->mergeDataObjects(
                    ProfileAddressInterface::class,
                    $profileAddress,
                    $address
                );
            }
            $addressesToUpdate[] = $profileAddress;
        }
        $profile->setAddresses($addressesToUpdate);
        return $profile;
    }

    /**
     * Place order
     *
     * @param OrderInterface|Order $order
     * @return OrderInterface
     */
    private function place(OrderInterface $order)
    {
        /** @var OrderPaymentInterface|Payment $payment */
        $payment = $order->getPayment();
        $methodInstance = $payment->getMethodInstance();
        $methodInstance->setStore($order->getStoreId());
        $methodInstance->validate();

        $orderState = Order::STATE_NEW;
        $orderStatus = $methodInstance->getConfigData('order_status');
        $order->setState($orderState)
            ->setStatus($orderStatus);

        $isCustomerNotified = $order->getCustomerNoteNotify();

        if (!array_key_exists($orderStatus, $order->getConfig()->getStateStatuses($orderState))) {
            $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
        }

        $message = $order->getCustomerNote();
        $originalOrderState = $order->getState();
        $originalOrderStatus = $order->getStatus();

        switch (true) {
            case ($message && ($originalOrderState == Order::STATE_PAYMENT_REVIEW)):
                $order->addStatusToHistory($originalOrderStatus, $message, $isCustomerNotified);
                break;
            case ($message):
            case ($originalOrderState && $message):
            case ($originalOrderState != $orderState):
            case ($originalOrderStatus != $orderStatus):
                $order->setState($orderState)
                    ->setStatus($orderStatus)
                    ->addStatusHistoryComment($message)
                    ->setIsCustomerNotified($isCustomerNotified);
                break;
            default:
                break;
        }

        return $this->orderRepository->save($order);
    }

    /**
     * Create invoice
     *
     * @param OrderInterface $order
     * @param ProfilePaymentInfoInterface $paymentInfo
     * @return \Magento\Sales\Model\Order\Invoice
     */
    private function createInvoice(OrderInterface $order, ProfilePaymentInfoInterface $paymentInfo)
    {
        $payment = $order->getPayment();
        $payment
            ->setTransactionId($paymentInfo->getTransactionId())
            ->setCurrencyCode($paymentInfo->getBaseCurrencyCode())
            ->setParentTransactionId('')
            ->setIsTransactionClosed(0)
            ->registerCaptureNotification($paymentInfo->getBaseGrandTotal(), true);

        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $order->prepareInvoice()->register();
        $invoice
            ->setOrder($order)
            ->setState(Invoice::STATE_PAID);

        $order->addRelatedObject($invoice);

        $payment
            ->setCreatedInvoice($invoice)
            ->setShouldCloseParentTransaction(true);

        $this->orderRepository->save($order);
        return $invoice;
    }
}
