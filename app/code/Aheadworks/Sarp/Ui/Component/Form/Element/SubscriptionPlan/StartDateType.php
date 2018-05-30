<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Ui\Component\Form\Element\SubscriptionPlan;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\Provider as DataSourceProvider;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\StartDateType as StartDateTypeSource;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Input;

/**
 * Class StartDateType
 * @package Aheadworks\Sarp\Ui\Component\Form\Element\SubscriptionPlan
 */
class StartDateType extends Input
{
    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var DataSourceProvider
     */
    private $dataSourceProvider;

    /**
     * @param ContextInterface $context
     * @param EngineMetadataPool $engineMetadataPool
     * @param DataSourceProvider $dataSourceProvider
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        EngineMetadataPool $engineMetadataPool,
        DataSourceProvider $dataSourceProvider,
        $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->engineMetadataPool = $engineMetadataPool;
        $this->dataSourceProvider = $dataSourceProvider;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (!isset($config['optionsPerEngine'])) {
            $optionsPerEngine = [];
            foreach ($this->engineMetadataPool->getEnginesCodes() as $engineCode) {
                $dataSource = $this->dataSourceProvider->getDataSource(
                    SubscriptionPlanInterface::START_DATE_TYPE,
                    $engineCode
                );
                $optionsPerEngine[$engineCode] = $dataSource->toOptionArray();
            }
            $config['optionsPerEngine'] = $optionsPerEngine;
        }
        $config['showDayOfMonthInputSwitchValue'] = StartDateTypeSource::EXACT_DAY_OF_MONTH;
        $this->setData('config', $config);
        parent::prepare();
    }
}
