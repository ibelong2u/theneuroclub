<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Order\Payment;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Magento\Framework\DataObject\Copy;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class Initializer
 * @package Aheadworks\Sarp\Model\Order\Payment
 */
class Initializer
{
    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param EngineMetadataPool $engineMetadataPool
     * @param Copy $objectCopyService
     */
    public function __construct(
        EngineMetadataPool $engineMetadataPool,
        Copy $objectCopyService
    ) {
        $this->engineMetadataPool = $engineMetadataPool;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Init order payment
     *
     * @param OrderPaymentInterface|Payment $payment
     * @param ProfileInterface $profile
     * @param ProfilePaymentInfoInterface $paymentInfo
     * @param OrderInterface $order
     * @return OrderPaymentInterface
     */
    public function init(
        OrderPaymentInterface $payment,
        ProfileInterface $profile,
        ProfilePaymentInfoInterface $paymentInfo,
        OrderInterface $order
    ) {
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_profile_payment_info',
            'to_order_payment',
            $paymentInfo,
            $payment
        );
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_profile_payment_info',
            'to_order_payment_paid',
            $paymentInfo,
            $payment
        );

        $payment
            ->setMethod($this->getPaymentMethodCode($profile))
            ->setIsTransactionClosed(false)
            ->setOrder($order);

        return $payment;
    }

    /**
     * Retrieve payment method code
     *
     * @param ProfileInterface $profile
     * @return string
     */
    private function getPaymentMethodCode(ProfileInterface $profile)
    {
        return (empty($profile->getPaymentMethodCode()) ? $profile->getEngineCode() : $profile->getPaymentMethodCode());
    }
}
