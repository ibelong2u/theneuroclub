<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Plugin\Order;

use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\Logger\LoggerInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Observer\CcDataAssignObserver;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class InvoicePlugin
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Plugin\Order
 */
class InvoicePlugin
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param LoggerInterface $logger
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        LoggerInterface $logger,
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->logger = $logger;
        $this->profileRepository = $profileRepository;
    }

    /**
     * @param Invoice $subject
     * @param Invoice $invoice
     * @return Invoice
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPay(Invoice $subject, Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $additionalInfo = $order->getPayment()->getAdditionalInformation();
        if (isset($additionalInfo[CcDataAssignObserver::IS_AW_SARP_RECURRING_PAYMENT])
            && isset($additionalInfo[CcDataAssignObserver::AW_SARP_PROFILE_REFERENCE_ID])
        ) {
            $isRecurringPayment = $additionalInfo[CcDataAssignObserver::IS_AW_SARP_RECURRING_PAYMENT];
            if ($isRecurringPayment) {
                $referenceId = $additionalInfo[CcDataAssignObserver::AW_SARP_PROFILE_REFERENCE_ID];
                $profile = $this->profileRepository->getByReferenceId($referenceId);
                $this->logger->notice(
                    $profile,
                    LoggerInterface::ENTRY_TYPE_PAYMENT_CAPTURED,
                    ['order' => $order] // how to get invoice Id?
                );
            }
        }
        return $invoice;
    }
}
