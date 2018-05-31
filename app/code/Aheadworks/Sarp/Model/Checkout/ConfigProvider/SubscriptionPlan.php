<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Checkout\ConfigProvider;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Api\SubscriptionPlanRepositoryInterface;
use Aheadworks\Sarp\Model\Checkout\ConfigProviderInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\BillingFrequency as BillingFrequencySource;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\BillingPeriod as BillingPeriodSource;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\DayOfMonth\Ending;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\RepeatPayments as RepeatPaymentsSource;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\RepeatPayments\Converter as RepeatPaymentsConverter;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\StartDateType as StartDateTypeSource;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\Status;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SubscriptionPlan
 * @package Aheadworks\Sarp\Model\Checkout\ConfigProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriptionPlan implements ConfigProviderInterface
{
    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $subscriptionPlanRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RepeatPaymentsSource
     */
    private $repeatPaymentsSource;

    /**
     * @var BillingFrequencySource
     */
    private $billingFrequencySource;

    /**
     * @var BillingPeriodSource
     */
    private $billingPeriodSource;

    /**
     * @var StartDateTypeSource
     */
    private $startDateTypeSource;

    /**
     * @var Ending
     */
    private $ending;

    /**
     * @var RepeatPaymentsConverter
     */
    private $repeatPaymentsConverter;

    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * SubscriptionPlan constructor.
     * @param SubscriptionPlanRepositoryInterface $subscriptionPlanRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param RepeatPaymentsSource $repeatPaymentsSource
     * @param BillingFrequencySource $billingFrequencySource
     * @param BillingPeriodSource $billingPeriodSource
     * @param StartDateTypeSource $startDateTypeSource
     * @param Ending $ending
     * @param RepeatPaymentsConverter $repeatPaymentsConverter
     * @param EngineMetadataPool $engineMetadataPool
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        SubscriptionPlanRepositoryInterface $subscriptionPlanRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        RepeatPaymentsSource $repeatPaymentsSource,
        BillingFrequencySource $billingFrequencySource,
        BillingPeriodSource $billingPeriodSource,
        StartDateTypeSource $startDateTypeSource,
        Ending $ending,
        RepeatPaymentsConverter $repeatPaymentsConverter,
        EngineMetadataPool $engineMetadataPool
    ) {
        $this->subscriptionPlanRepository = $subscriptionPlanRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->repeatPaymentsSource = $repeatPaymentsSource;
        $this->billingFrequencySource = $billingFrequencySource;
        $this->billingPeriodSource = $billingPeriodSource;
        $this->startDateTypeSource = $startDateTypeSource;
        $this->ending = $ending;
        $this->repeatPaymentsConverter = $repeatPaymentsConverter;
        $this->engineMetadataPool = $engineMetadataPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return ['subscriptionPlans' => $this->getSubscriptionPlansData()];
    }

    /**
     * Get subscription plans data
     *
     * @return array
     */
    private function getSubscriptionPlansData()
    {
        $data = [];

        // todo: add filter by compatibility with current cart items.
        //       Will be revised in the scope of https://aheadworks.atlassian.net/browse/M2SARP-21
        $this->searchCriteriaBuilder
            ->addFilter(SubscriptionPlanInterface::STATUS, Status::ENABLED)
            ->addFilter(
                SubscriptionPlanInterface::WEBSITE_ID,
                $this->storeManager->getWebsite()->getId()
            )
            ->addFilter(
                SubscriptionPlanInterface::ENGINE_CODE,
                $this->engineMetadataPool->getEnginesCodes(true),
                'in'
            );
        $subscriptionPlans = $this->subscriptionPlanRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        foreach ($subscriptionPlans->getItems() as $plan) {
            $data[] = array_merge(
                $this->dataObjectProcessor->buildOutputDataArray(
                    $plan,
                    SubscriptionPlanInterface::class
                ),
                [
                    'storefront_description' => $this->prepareStoreFrontDescription($plan),
                    'number_of_payments' => $plan->getTotalBillingCycles() ? : __('Infinite'),
                    'repeat' => $this->prepareRepeatValue($plan),
                    'start' => $this->prepareStartValue($plan)
                ]
            );
        }

        return $data;
    }

    /**
     * Prepare storefront description
     *
     * @param SubscriptionPlanInterface $plan
     * @return string
     */
    private function prepareStoreFrontDescription(SubscriptionPlanInterface $plan)
    {
        if ($plan->getStorefrontDescription()) {
            return preg_replace(
                '#<script[^>]*>.*?</script>#is',
                '',
                $plan->getStorefrontDescription()
            );
        }
        return '';
    }

    /**
     * Prepare repeat value
     *
     * @param SubscriptionPlanInterface $plan
     * @return \Magento\Framework\Phrase
     */
    private function prepareRepeatValue(SubscriptionPlanInterface $plan)
    {
        // todo: use engine data source providers or fix
        $repeatPaymentsOptions = $this->repeatPaymentsSource->getOptions();
        $billingFrequencyOptions = $this->billingFrequencySource->getOptions();
        $billingPeriodOptions = $this->billingPeriodSource->getOptions();

        $billingFrequency = $plan->getBillingFrequency();
        $billingPeriod = $plan->getBillingPeriod();
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
     * Prepare start value
     *
     * @param SubscriptionPlanInterface $plan
     * @return \Magento\Framework\Phrase
     */
    private function prepareStartValue(SubscriptionPlanInterface $plan)
    {
        $startDateTypeOptions = $this->startDateTypeSource->getOptions();
        $startDateType = $plan->getStartDateType();
        if ($startDateType == StartDateTypeSource::EXACT_DAY_OF_MONTH) {
            $dayOfMonth = $plan->getStartDateDayOfMonth();
            return __('%1 day of month', $dayOfMonth . $this->ending->getEnding($dayOfMonth));
        }
        return $startDateTypeOptions[$startDateType];
    }
}
