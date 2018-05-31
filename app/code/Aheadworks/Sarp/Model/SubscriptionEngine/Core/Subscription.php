<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core;

use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Subscription as SubscriptionResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Subscription
 *
 * @method int getSubscriptionId()
 * @method Subscription setSubscriptionId(int $subscriptionId)
 * @method int getProfileId()
 * @method Subscription setProfileId(int $profileId)
 * @method bool getIsInitialPaid()
 * @method Subscription setIsInitialPaid(bool $isInitialPaid)
 * @method int getTrialPaymentsCount()
 * @method Subscription setTrialPaymentsCount(int $trialPaymentsCount)
 * @method int getRegularPaymentsCount()
 * @method Subscription setRegularPaymentsCount(int $regularPaymentsCount)
 * @method int getPaymentFailuresCount()
 * @method Subscription setPaymentFailuresCount(int $paymentFailuresCount)
 * @method array getPaymentData()
 * @method Subscription setPaymentData(array $paymentData)
 * @method bool getIsReactivated()
 * @method Subscription setIsReactivated(bool $isReactivated)
 * @method string getStartDate()
 * @method Subscription setStartDate(string $startDate)
 * @method string getStatus()
 * @method Subscription setStatus(string $status)
 * @method string getEngineCode()
 * @method Subscription setEngineCode(string $engineCode)
 * @method bool getIsInitialFeeEnabled()
 * @method Subscription setIsInitialFeeEnabled(bool $flag)
 * @method bool getIsTrialPeriodEnabled()
 * @method Subscription setIsTrialPeriodEnabled(bool $flag)
 * @method bool getTotalBillingCycles()
 * @method Subscription setTotalBillingCycles(bool $totalBillingCycles)
 * @method bool getTrialTotalBillingCycles()
 * @method Subscription setTrialTotalBillingCycles(bool $trialTotalBillingCycles)
 * @method int getBillingPeriod()
 * @method Subscription setBillingPeriod(int $billingPeriod)
 * @method int getBillingFrequency()
 * @method Subscription setBillingFrequency(int $billingFrequency)
 *
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core
 */
class Subscription extends AbstractModel
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(SubscriptionResource::class);
    }
}
