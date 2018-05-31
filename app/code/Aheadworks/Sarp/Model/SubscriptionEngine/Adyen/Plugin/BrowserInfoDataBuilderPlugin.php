<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Plugin;

use Adyen\Payment\Gateway\Request\BrowserInfoDataBuilder;
use Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Observer\CcDataAssignObserver;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class BrowserInfoDataBuilderPlugin
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Plugin
 */
class BrowserInfoDataBuilderPlugin
{
    /**
     * @param BrowserInfoDataBuilder $subject
     * @param \Closure $proceed
     * @param array $buildSubject
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBuild(BrowserInfoDataBuilder $subject, \Closure $proceed, array $buildSubject)
    {
        $result = [];

        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $isRecurringPayment = $payment->getAdditionalInformation(CcDataAssignObserver::IS_AW_SARP_RECURRING_PAYMENT);
        if (!$isRecurringPayment) {
            $result = $proceed($buildSubject);
        }

        return $result;
    }
}
