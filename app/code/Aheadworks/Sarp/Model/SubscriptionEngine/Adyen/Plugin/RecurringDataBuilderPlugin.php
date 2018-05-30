<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Plugin;

use Adyen\Payment\Gateway\Request\RecurringDataBuilder;
use Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Observer\CcDataAssignObserver;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class RecurringDataBuilderPlugin
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Plugin
 */
class RecurringDataBuilderPlugin
{
    /**
     * @param RecurringDataBuilder $subject
     * @param \Closure $proceed
     * @param array $buildSubject
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBuild(RecurringDataBuilder $subject, \Closure $proceed, array $buildSubject)
    {
        $result = [];

        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $isRecurringPayment = $payment->getAdditionalInformation(CcDataAssignObserver::IS_AW_SARP_RECURRING_PAYMENT);
        if ($isRecurringPayment) {
            $result['recurring'] = ['contract' => 'RECURRING'];
            $result['shopperInteraction'] = 'ContAuth';
        } else {
            $result = $proceed($buildSubject);
        }

        return $result;
    }
}
