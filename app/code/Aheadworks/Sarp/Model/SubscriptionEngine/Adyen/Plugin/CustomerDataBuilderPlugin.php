<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Plugin;

use Adyen\Payment\Gateway\Request\CustomerDataBuilder;
use Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Data\ShopperReferenceBuilder;
use Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Observer\CcDataAssignObserver;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class CustomerDataBuilderPlugin
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Plugin
 */
class CustomerDataBuilderPlugin
{
    /**
     * @var ShopperReferenceBuilder
     */
    private $shopperReferenceBuilder;

    /**
     * @param ShopperReferenceBuilder $shopperReferenceBuilder
     */
    public function __construct(ShopperReferenceBuilder $shopperReferenceBuilder)
    {
        $this->shopperReferenceBuilder = $shopperReferenceBuilder;
    }

    /**
     * @param CustomerDataBuilder $subject
     * @param \Closure $proceed
     * @param array $buildSubject
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBuild(CustomerDataBuilder $subject, \Closure $proceed, array $buildSubject)
    {
        $result = [];

        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $isRecurringPayment = $payment->getAdditionalInformation(CcDataAssignObserver::IS_AW_SARP_RECURRING_PAYMENT);
        if ($isRecurringPayment) {
            $order = $paymentDataObject->getOrder();
            $result['shopperReference'] = $this->shopperReferenceBuilder->build(
                $payment->getAdditionalInformation(CcDataAssignObserver::AW_SARP_PROFILE_REFERENCE_ID),
                $order->getStoreId(),
                $order->getCustomerId()
            );
            $result['shopperEmail'] = $order->getBillingAddress()->getEmail();
        } else {
            $result = $proceed($buildSubject);
        }

        return $result;
    }
}
