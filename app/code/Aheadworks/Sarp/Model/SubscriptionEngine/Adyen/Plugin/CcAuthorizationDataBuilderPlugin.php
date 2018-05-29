<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Plugin;

use Adyen\Payment\Gateway\Request\CcAuthorizationDataBuilder;
use Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Observer\CcDataAssignObserver;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class CcAuthorizationDataBuilderPlugin
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Plugin
 */
class CcAuthorizationDataBuilderPlugin
{
    /**
     * @param CcAuthorizationDataBuilder $subject
     * @param \Closure $proceed
     * @param array $buildSubject
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBuild(CcAuthorizationDataBuilder $subject, \Closure $proceed, array $buildSubject)
    {
        $result = [];

        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $isRecurringPayment = $payment->getAdditionalInformation(CcDataAssignObserver::IS_AW_SARP_RECURRING_PAYMENT);
        $recurringDetailReference = $payment->getAdditionalInformation(
            CcDataAssignObserver::AW_SARP_SELECTED_RECURRING_DETAILS_REFERENCE
        );
        if ($isRecurringPayment && $recurringDetailReference) {
            $result['selectedRecurringDetailReference'] = $recurringDetailReference;
        } else {
            $result = $proceed($buildSubject);
        }

        return $result;
    }
}
