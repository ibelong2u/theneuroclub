<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\CC;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\Order\Converter as OrderConverter;
use Aheadworks\Sarp\Model\Order\InventoryManagement;
use Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Data\ShopperReferenceBuilder;
use Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Observer\CcDataAssignObserver;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Exception\PaymentException;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment as CorePayment;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionResult;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\SalesSequence\Model\Manager as SequenceManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class PaymentAction
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\CC
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentAction implements ActionInterface
{
    /**
     * Class name for payment request api
     */
    const PAYMENT_REQUEST_API_CLASS = 'Adyen\Payment\Model\Api\PaymentRequest';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderConverter
     */
    private $orderConverter;

    /**
     * @var InventoryManagement
     */
    private $inventoryManagement;

    /**
     * @var SequenceManager
     */
    private $sequenceManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ShopperReferenceBuilder
     */
    private $shopperReferenceBuilder;

    /**
     * @var ActionResultFactory
     */
    private $resultFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderConverter $orderConverter
     * @param InventoryManagement $inventoryManagement
     * @param SequenceManager $sequenceManager
     * @param StoreManagerInterface $storeManager
     * @param DataObjectFactory $dataObjectFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param ShopperReferenceBuilder $shopperReferenceBuilder
     * @param ActionResultFactory $resultFactory
     * @param ObjectManagerInterface $objectManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderConverter $orderConverter,
        InventoryManagement $inventoryManagement,
        SequenceManager $sequenceManager,
        StoreManagerInterface $storeManager,
        DataObjectFactory $dataObjectFactory,
        ProfileRepositoryInterface $profileRepository,
        ShopperReferenceBuilder $shopperReferenceBuilder,
        ActionResultFactory $resultFactory,
        ObjectManagerInterface $objectManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderConverter = $orderConverter;
        $this->inventoryManagement = $inventoryManagement;
        $this->sequenceManager = $sequenceManager;
        $this->storeManager = $storeManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->profileRepository = $profileRepository;
        $this->shopperReferenceBuilder = $shopperReferenceBuilder;
        $this->resultFactory = $resultFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function pay(
        ProfileInterface $profile,
        ProfilePaymentInfoInterface $paymentInfo,
        array $additionalData
    ) {
        $order = $this->orderConverter->fromProfile($profile, $paymentInfo);
        try {
            $this->inventoryManagement->subtract($profile);
            $order = $this->placeOrder($profile, $order, $additionalData);
        } catch (\Exception $e) {
            $this->inventoryManagement->revert($profile);
            throw $e;
        }

        $profile
            ->setLastOrderId($order->getEntityId())
            ->setLastOrderDate($order->getCreatedAt());
        $this->profileRepository->save($profile, $order->getEntityId());

        return $this->resultFactory->create(
            [
                ActionResult::ORDER => $order,
                ActionResult::STATUS => CorePayment::STATUS_PAID
            ]
        );
    }

    /**
     * Place order
     *
     * @param ProfileInterface $profile
     * @param OrderInterface|Order $order
     * @param array $additionalPaymentData
     * @return OrderInterface
     * @throws LocalizedException
     */
    private function placeOrder(ProfileInterface $profile, OrderInterface $order, $additionalPaymentData)
    {
        $storeId = $order->getStoreId();
        $shopperReference = $this->shopperReferenceBuilder->buildUsingProfile($profile);
        if (class_exists(self::PAYMENT_REQUEST_API_CLASS)) {
            $paymentRequestApi = $this->objectManager->create(self::PAYMENT_REQUEST_API_CLASS);
            $recurringContracts = $paymentRequestApi->getRecurringContractsForShopper($shopperReference, $storeId);

            /** @var Payment $payment */
            $payment = $order->getPayment();
            $additionalPaymentData = array_merge(
                $additionalPaymentData,
                [
                    CcDataAssignObserver::IS_AW_SARP_RECURRING_PAYMENT => true,
                    CcDataAssignObserver::AW_SARP_PROFILE_REFERENCE_ID => $profile->getReferenceId(),
                    CcDataAssignObserver::AW_SARP_SELECTED_RECURRING_DETAILS_REFERENCE => count($recurringContracts) > 0
                        ? current($recurringContracts)['recurringDetailReference']
                        : null
                ]
            );
            $paymentData = $this->dataObjectFactory->create(
                [
                    PaymentInterface::KEY_METHOD => $order->getPayment()->getMethod(),
                    PaymentInterface::KEY_PO_NUMBER => null,
                    PaymentInterface::KEY_ADDITIONAL_DATA => $additionalPaymentData,
                    'checks' => []
                ]
            );
            $payment->getMethodInstance()->assignData($paymentData);

            $store = $this->storeManager->getStore($storeId);
            $group = $this->storeManager->getGroup($store->getStoreGroupId());
            $sequence = $this->sequenceManager->getSequence(Order::ENTITY, $group->getDefaultStoreId());
            $incrementId = $sequence->getNextValue();

            $order
                ->setIncrementId($incrementId)
                ->setTotalPaid(0)
                ->setBaseTotalPaid(0);
            $order->place();
            $this->orderRepository->save($order);
        }

        return $order;
    }
}
