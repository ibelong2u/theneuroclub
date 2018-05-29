<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Scheduler;

use Aheadworks\Sarp\Model\Profile\PaymentInfo;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\DataResolver\NextPaymentDate;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;

/**
 * Class PaymentTypesEstimation
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Scheduler
 */
class PaymentTypesEstimation
{
    /**
     * @var CoreDate
     */
    private $coreDate;

    /**
     * @var NextPaymentDate
     */
    private $nextPaymentDate;

    /**
     * @param CoreDate $coreDate
     * @param NextPaymentDate $nextPaymentDate
     */
    public function __construct(
        CoreDate $coreDate,
        NextPaymentDate $nextPaymentDate
    ) {
        $this->coreDate = $coreDate;
        $this->nextPaymentDate = $nextPaymentDate;
    }

    /**
     * Estimate possible payment types of subscription for current date.
     * Assumed that current date is a payment date candidate.
     * Returns an empty array if there is no possible payments
     *
     * @param Subscription $subscription
     * @param string $currentDate
     * @param string|null $lastPaymentDate
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function estimate(Subscription $subscription, $currentDate, $lastPaymentDate = null)
    {
        $paymentTypes = [];

        $wasPayments = $lastPaymentDate !== null;
        $baseDate = $wasPayments
            ? $lastPaymentDate
            : $subscription->getStartDate();

        $baseTm = $this->coreDate->gmtTimestamp($baseDate);
        $currentTm = $this->coreDate->gmtTimestamp($currentDate);

        $estimateTypes = $wasPayments
            ? $currentTm >= $this->coreDate->gmtTimestamp(
                $this->nextPaymentDate->getDateNext(
                    $lastPaymentDate,
                    $subscription->getBillingPeriod(),
                    $subscription->getBillingFrequency()
                )
            )
            : true;

        if ($estimateTypes) {
            if ($subscription->getIsInitialFeeEnabled() && !$subscription->getIsInitialPaid()) {
                $paymentTypes[] = PaymentInfo::PAYMENT_TYPE_INITIAL;
            }
            if ($baseTm <= $currentTm) {
                if ($subscription->getIsTrialPeriodEnabled()
                    && $subscription->getTrialPaymentsCount() < $subscription->getTrialTotalBillingCycles()
                ) {
                    $paymentTypes[] = PaymentInfo::PAYMENT_TYPE_TRIAL;
                } else {
                    $totalRegular = $subscription->getTotalBillingCycles();
                    if ($totalRegular == 0 || $subscription->getRegularPaymentsCount() < $totalRegular) {
                        $paymentTypes[] = PaymentInfo::PAYMENT_TYPE_REGULAR;
                    }
                }
            }
        }

        return $paymentTypes;
    }
}
