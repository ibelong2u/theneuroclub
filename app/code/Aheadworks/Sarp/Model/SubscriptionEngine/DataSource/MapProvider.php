<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\DataSource;

use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;

/**
 * Class MapProvider
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\DataSource
 */
class MapProvider
{
    /**
     * @var MapFactory
     */
    private $mapFactory;

    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var array
     */
    private $mapInstances = [];

    /**
     * @param MapFactory $mapFactory
     * @param EngineMetadataPool $engineMetadataPool
     */
    public function __construct(
        MapFactory $mapFactory,
        EngineMetadataPool $engineMetadataPool
    ) {
        $this->mapFactory = $mapFactory;
        $this->engineMetadataPool = $engineMetadataPool;
    }

    /**
     * Get data map for 'fromField' to 'toField' mapping and specific engine
     *
     * @param string $fromField
     * @param string $toField
     * @param string $engineCode
     * @return MapInterface|null
     * @throws \Exception
     */
    public function getMap($fromField, $toField, $engineCode)
    {
        $key = $fromField . '-' . $toField . '-' . $engineCode;
        if (!isset($this->mapInstances[$key])) {
            $metadata = $this->engineMetadataPool->getMetadata($engineCode);
            $dataSourceMaps = $metadata->getDataSourceMaps();
            if (isset($dataSourceMaps[$fromField])
                && isset($dataSourceMaps[$fromField][$toField])
            ) {
                $this->mapInstances[$key] = $this->mapFactory->create($dataSourceMaps[$fromField][$toField]);
            } else {
                $this->mapInstances[$key] = null;
            }
        }
        return $this->mapInstances[$key];
    }
}
