<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class EnginePool
 * @package Aheadworks\Sarp\Model\SubscriptionEngine
 */
class EnginePool
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var EngineInterface[]
     */
    private $engineInstances = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param EngineMetadataPool $engineMetadataPool
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        EngineMetadataPool $engineMetadataPool
    ) {
        $this->objectManager = $objectManager;
        $this->engineMetadataPool = $engineMetadataPool;
    }

    /**
     * Retrieves subscription engine instance
     *
     * @param string $engineCode
     * @return EngineInterface
     * @throws \Exception
     */
    public function getEngine($engineCode)
    {
        if (!isset($this->engineInstances[$engineCode])) {
            $metadata = $this->engineMetadataPool->getMetadata($engineCode);
            $engineInstance = $this->objectManager->create($metadata->getClassName());
            if (!$engineInstance instanceof EngineInterface) {
                throw new \Exception(
                    sprintf('Subscription engine %s does not implement required interface.', $engineCode)
                );
            }
            $this->engineInstances[$engineCode] = $engineInstance;
        }
        return $this->engineInstances[$engineCode];
    }
}
