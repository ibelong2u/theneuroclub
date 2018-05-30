<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Customer\Subscription\Info;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\BillingFrequency as BillingFrequencySource;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\BillingPeriod as BillingPeriodSource;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\RepeatPayments as RepeatPaymentsSource;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\RepeatPayments\Converter as RepeatPaymentsConverter;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Plan
 * @package Aheadworks\Sarp\Block\Customer\Subscription\Info
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Plan extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

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
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param Session $customerSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param RepeatPaymentsConverter $repeatPaymentsConverter
     * @param BillingPeriodSource $billingPeriodSource
     * @param BillingFrequencySource $billingFrequencySource
     * @param RepeatPaymentsSource $repeatPaymentsSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        Session $customerSession,
        PriceCurrencyInterface $priceCurrency,
        RepeatPaymentsConverter $repeatPaymentsConverter,
        BillingPeriodSource $billingPeriodSource,
        BillingFrequencySource $billingFrequencySource,
        RepeatPaymentsSource $repeatPaymentsSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileRepository = $profileRepository;
        $this->customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        $this->repeatPaymentsConverter = $repeatPaymentsConverter;
        $this->billingPeriodSource = $billingPeriodSource;
        $this->billingFrequencySource = $billingFrequencySource;
        $this->repeatPaymentsSource = $repeatPaymentsSource;
    }

    /**
     * Get profile ID
     *
     * @return int|null
     */
    private function getProfileId()
    {
        return $this->getRequest()->getParam('profile_id');
    }

    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profileRepository->get($this->getProfileId());
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
        if (!$this->getProfileId() || !$this->customerSession->isLoggedIn()) {
            return '';
        }
        return parent::_toHtml();
    }
}
