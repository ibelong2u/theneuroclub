<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class CcDataAssignObserver
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Observer
 */
class CcDataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * Recurring payment flag
     */
    const IS_AW_SARP_RECURRING_PAYMENT = 'is_aw_sarp_recurring_payment';

    /**
     * Recurring profile reference Id
     */
    const AW_SARP_PROFILE_REFERENCE_ID = 'aw_sarp_profile_reference_id';

    /**
     * Selected recurring details reference
     */
    const AW_SARP_SELECTED_RECURRING_DETAILS_REFERENCE = 'aw_sarp_selected_recurring_detail_reference';

    /**
     * @var array
     */
    private $additionalInformationList = [
        self::IS_AW_SARP_RECURRING_PAYMENT,
        self::AW_SARP_PROFILE_REFERENCE_ID,
        self::AW_SARP_SELECTED_RECURRING_DETAILS_REFERENCE
    ];

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);
        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}
