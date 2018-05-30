<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Adminhtml\Subscription\Info;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\BillingFrequency as BillingFrequencySource;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\BillingPeriod as BillingPeriodSource;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\RepeatPayments as RepeatPaymentsSource;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\RepeatPayments\Converter as RepeatPaymentsConverter;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Plan
 * @package Aheadworks\Sarp\Block\Adminhtml\Subscription\Info
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Plan extends \Magento\Backend\Block\Template
{
    /**
     * @var RepeatPaymentsConverter
     */
    private $repeatPaymentsConverter;

    /**
     * @var BillingPeriodSource
     */
    private $billingPeriodSource;

    /**
     * @var BillingFrequencySource
     */
    private $billingFrequencySource;

    /**
     * @var RepeatPaymentsSource
     */
    private $repeatPaymentsSource;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp::subscription/info/plan.phtml';

    /**
     * @param Context $context
     * @param RepeatPaymentsConverter $repeatPaymentsConverter
     * @param BillingPeriodSource $billingPeriodSource
     * @param BillingFrequencySource $billingFrequencySource
     * @param RepeatPaymentsSource $repeatPaymentsSource
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        RepeatPaymentsConverter $repeatPaymentsConverter,
        BillingPeriodSource $billingPeriodSource,
        BillingFrequencySource $billingFrequencySource,
        RepeatPaymentsSource $repeatPaymentsSource,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->repeatPaymentsConverter = $repeatPaymentsConverter;
        $this->billingPeriodSource = $billingPeriodSource;
        $this->billingFrequencySource = $billingFrequencySource;
        $this->repeatPaymentsSource = $repeatPaymentsSource;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get profile entity
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set profile entity
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * Format repeat payments value
     *
     * @param ProfileInterface $profile
     * @return \Magento\Framework\Phrase
     */
    public function formatRepeatValue($profile)
    {
        $repeatPaymentsOptions = $this->repeatPaymentsSource->getOptions();
        $billingFrequencyOptions = $this->billingFrequencySource->getOptions();
        $billingPeriodOptions = $this->billingPeriodSource->getOptions();

        $billingFrequency = $profile->getBillingFrequency();
        $billingPeriod = $profile->getBillingPeriod();
        $repeatPayments = $this->repeatPaymentsConverter->toRepeatPayments($billingPeriod, $billingFrequency);
        if ($repeatPayments) {
            return $repeatPaymentsOptions[$repeatPayments];
        }

        return __(
            'Every %1 %2',
            $billingFrequencyOptions[$billingFrequency],
            $billingPeriodOptions[$billingPeriod]
        );
    }

    /**
     * Get subscription plan edit url
     *
     * @param int $planId
     * @return string
     */
    public function getPlanEditUrl($planId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp/subscriptionplan/edit',
            ['subscription_plan_id' => $planId]
        );
    }

    /**
     * Get admin date
     *
     * @param string $date
     * @return \DateTime
     */
    public function getAdminDate($date)
    {
        return $this->_localeDate->date(new \DateTime($date));
    }

    /**
     * Format profile amount
     *
     * @param float $amount
     * @param string $currencyCode
     * @return float
     */
    public function formatProfileAmount($amount, $currencyCode)
    {
        return $this->priceCurrency->format($amount, true, 2, null, $currencyCode);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getProfile()) {
            return '';
        }
        return parent::_toHtml();
    }
}
