<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Ui\Component\Form\Element\SubscriptionPlan;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\MapProvider;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\Provider as DataSourceProvider;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\RepeatPayments as RepeatPaymentsSource;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\RepeatPayments\Converter as RepeatPaymentsConverter;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Input;

/**
 * Class RepeatPayments
 * @package Aheadworks\Sarp\Ui\Component\Form\Element\SubscriptionPlan
 */
class RepeatPayments extends Input
{
    /**
     * @var RepeatPaymentsSource
     */
    private $repeatPaymentsSource;

    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var DataSourceProvider
     */
    private $dataSourceProvider;

    /**
     * @var MapProvider
     */
    private $dataSourceMapProvider;

    /**
     * @var RepeatPaymentsConverter
     */
    private $repeatPaymentsConverter;

    /**
     * @param ContextInterface $context
     * @param RepeatPaymentsSource $repeatPaymentsSource
     * @param EngineMetadataPool $engineMetadataPool
     * @param DataSourceProvider $dataSourceProvider
     * @param MapProvider $dataSourceMapProvider
     * @param RepeatPaymentsConverter $repeatPaymentsConverter
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        RepeatPaymentsSource $repeatPaymentsSource,
        EngineMetadataPool $engineMetadataPool,
        DataSourceProvider $dataSourceProvider,
        MapProvider $dataSourceMapProvider,
        RepeatPaymentsConverter $repeatPaymentsConverter,
        $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->repeatPaymentsSource = $repeatPaymentsSource;
        $this->engineMetadataPool = $engineMetadataPool;
        $this->dataSourceProvider = $dataSourceProvider;
        $this->dataSourceMapProvider = $dataSourceMapProvider;
        $this->repeatPaymentsConverter = $repeatPaymentsConverter;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (!isset($config['billingPeriodOptionsPerEngine'])) {
            $config['billingPeriodOptionsPerEngine'] = $this->getOptionsPerEngine(
                SubscriptionPlanInterface::BILLING_PERIOD
            );
        }
        if (!isset($config['billingPeriodToFreqMapPerEngine'])) {
            $config['billingPeriodToFreqMapPerEngine'] = $this->getMapPerEngine(
                SubscriptionPlanInterface::BILLING_PERIOD,
                SubscriptionPlanInterface::BILLING_FREQUENCY
            );
        }
        if (!isset($config['repeatPaymentsToValuesMap'])) {
            $config['repeatPaymentsToValuesMap'] = [];
            $repeatPaymentsValues = [
                RepeatPaymentsSource::DAILY,
                RepeatPaymentsSource::WEEKLY,
                RepeatPaymentsSource::MONTHLY
            ];
            foreach ($repeatPaymentsValues as $repeatPayments) {
                $config['repeatPaymentsToValuesMap'][$repeatPayments] = [
                    'billingPeriod' => $this->repeatPaymentsConverter->toBillingPeriod($repeatPayments),
                    'billingFrequency' => $this->repeatPaymentsConverter->toBillingFrequency($repeatPayments)
                ];
            }
        }
        if (!isset($config['repeatPaymentsOptionsPerEngine'])) {
            $config['repeatPaymentsOptionsPerEngine'] = $this->getRepeatPaymentsOptionsPerEngine();
        }
        $config['expandOptionValue'] = RepeatPaymentsSource::EVERY;

        $this->setData('config', $config);
        parent::prepare();
    }

    /**
     * Get field value options per engine
     *
     * @param string $fieldName
     * @return array
     */
    private function getOptionsPerEngine($fieldName)
    {
        $optionsPerEngine = [];
        foreach ($this->engineMetadataPool->getEnginesCodes() as $engineCode) {
            $dataSource = $this->dataSourceProvider->getDataSource($fieldName, $engineCode);
            $optionsPerEngine[$engineCode] = $dataSource->toOptionArray();
        }
        return $optionsPerEngine;
    }

    /**
     * Get option maps per engine
     *
     * @param string $fromFieldName
     * @param string $toFieldName
     * @return array
     */
    private function getMapPerEngine($fromFieldName, $toFieldName)
    {
        $mapPerEngine = [];
        foreach ($this->engineMetadataPool->getEnginesCodes() as $engineCode) {
            $map = $this->dataSourceMapProvider->getMap($fromFieldName, $toFieldName, $engineCode);
            $mapPerEngine[$engineCode] = $map->toOptionMapArray();
        }
        return $mapPerEngine;
    }

    /**
     * Get repeat payments options per engine
     *
     * @return array
     */
    private function getRepeatPaymentsOptionsPerEngine()
    {
        $repeatPaymentsOptionsPerEngine = [];
        $repeatPaymentsOptions = $this->repeatPaymentsSource->toOptionArray();
        foreach ($this->engineMetadataPool->getEnginesCodes() as $engineCode) {
            $billingPeriodSource = $this->dataSourceProvider->getDataSource(
                SubscriptionPlanInterface::BILLING_PERIOD,
                $engineCode
            );
            $repeatEveryOption = null;
            foreach ($billingPeriodSource->toOptionArray() as $option) {
                $repeatValue = $this->repeatPaymentsConverter->toRepeatPayments($option['value'], 1);
                if ($this->isRepeatValueExists($repeatValue, $engineCode)) {
                    foreach ($repeatPaymentsOptions as $repeatOption) {
                        if ($repeatOption['value'] == $repeatValue) {
                            $repeatPaymentsOptionsPerEngine[$engineCode][] = $repeatOption;
                        }
                        if ($repeatOption['value'] == RepeatPaymentsSource::EVERY) {
                            $repeatEveryOption = $repeatOption;
                        }
                    }
                }
            }
            if ($repeatEveryOption) {
                $repeatPaymentsOptionsPerEngine[$engineCode][] = $repeatEveryOption;
            }
        }
        return $repeatPaymentsOptionsPerEngine;
    }

    /**
     * Check if repeat payments value exists for given engine code
     *
     * @param int $repeatValue
     * @param string $engineCode
     * @return bool
     */
    private function isRepeatValueExists($repeatValue, $engineCode)
    {
        $map = $this->dataSourceMapProvider->getMap(
            SubscriptionPlanInterface::BILLING_PERIOD,
            SubscriptionPlanInterface::BILLING_FREQUENCY,
            $engineCode
        );
        $mapArray = $map->toOptionMapArray();
        $billingPeriod = $this->repeatPaymentsConverter->toBillingPeriod($repeatValue);
        $billingFrequency = $this->repeatPaymentsConverter->toBillingFrequency($repeatValue);

        if (isset($mapArray[$billingPeriod])) {
            foreach ($mapArray[$billingPeriod] as $billingFrequencyOption) {
                if ($billingFrequencyOption['value'] == $billingFrequency) {
                    return true;
                }
            }
        }
        return false;
    }
}
