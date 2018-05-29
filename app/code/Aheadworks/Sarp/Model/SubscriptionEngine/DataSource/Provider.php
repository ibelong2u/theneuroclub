<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\DataSource;

use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Provider
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\DataSource
 */
class Provider
{
    /**
     * @var SourceFactory
     */
    private $sourceFactory;

    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var array
     */
    private $dataSourceInstances = [];

    /**
     * @param SourceFactory $sourceFactory
     * @param EngineMetadataPool $engineMetadataPool
     */
    public function __construct(
        SourceFactory $sourceFactory,
        EngineMetadataPool $engineMetadataPool
    ) {
        $this->sourceFactory = $sourceFactory;
        $this->engineMetadataPool = $engineMetadataPool;
    }

    /**
     * Get data source for field and specific engine
     *
     * @param string $field
     * @param string $engineCode
     * @return OptionSourceInterface|null
     * @throws \Exception
     */
    public function getDataSource($field, $engineCode)
    {
        $key = $field . '-' . $engineCode;
        if (!isset($this->dataSourceInstances[$key])) {
            $metadata = $this->engineMetadataPool->getMetadata($engineCode);
            $dataSources = $metadata->getDataSources();
            $this->dataSourceInstances[$key] = isset($dataSources[$field])
                ? $this->sourceFactory->create($dataSources[$field])
                : null;
        }
        return $this->dataSourceInstances[$key];
    }
}
