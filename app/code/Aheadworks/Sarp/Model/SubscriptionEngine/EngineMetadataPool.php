<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class EngineMetadataPool
 * @package Aheadworks\Sarp\Model\SubscriptionEngine
 */
class EngineMetadataPool
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var EngineAvailability
     */
    private $engineAvailability;

    /**
     * @var array
     */
    private $metadata = [];

    /**
     * @var EngineMetadataInterface[]
     */
    private $metadataInstances = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param EngineAvailability $engineAvailability
     * @param array $metadata
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        EngineAvailability $engineAvailability,
        $metadata = []
    ) {
        $this->objectManager = $objectManager;
        $this->engineAvailability = $engineAvailability;
        $this->metadata = $metadata;
    }

    /**
     * Retrieves metadata for engine code
     *
     * @param string $engineCode
     * @return EngineMetadataInterface
     * @throws \Exception
     */
    public function getMetadata($engineCode)
    {
        if (!isset($this->metadataInstances[$engineCode])) {
            if (!isset($this->metadata[$engineCode])) {
                throw new \Exception(sprintf('Unknown subscription engine metadata: %s requested', $engineCode));
            }
            $this->metadataInstances[$engineCode] = $this->objectManager->create(
                EngineMetadataInterface::class,
                ['data' => $this->metadata[$engineCode]]
            );
        }
        return $this->metadataInstances[$engineCode];
    }

    /**
     * Retrieves all engine codes
     *
     * @param bool $availableOnly
     * @return array
     */
    public function getEnginesCodes($availableOnly = true)
    {
        $engineCodes = array_keys($this->metadata);
        return $availableOnly
            ? array_filter($engineCodes, [$this, 'filterByAvailability'])
            : $engineCodes;
    }

    /**
     * Filter engine codes by availability
     *
     * @param string $engineCode
     * @return bool
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function filterByAvailability($engineCode)
    {
        $metadata = $this->getMetadata($engineCode);
        return $this->engineAvailability->isAvailable($metadata);
    }
}
