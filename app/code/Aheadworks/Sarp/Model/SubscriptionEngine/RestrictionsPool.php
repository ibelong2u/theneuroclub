<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine;

use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Restrictions\Provider as CoreRestrictionsProvider;

/**
 * Class RestrictionsPool
 * @package Aheadworks\Sarp\Model\SubscriptionEngine
 */
class RestrictionsPool
{
    /**
     * @var \Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsInterfaceFactory
     */
    private $restrictionsFactory;

    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var CoreRestrictionsProvider
     */
    private $coreRestrictionsProvider;

    /**
     * @var array
     */
    private $restrictions = [];

    /**
     * @var RestrictionsInterface[]
     */
    private $restrictionsInstances = [];

    /**
     * @param RestrictionsInterfaceFactory $restrictionsFactory
     * @param EngineMetadataPool $engineMetadataPool
     * @param CoreRestrictionsProvider $coreRestrictionsProvider
     * @param array $restrictions
     */
    public function __construct(
        \Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsInterfaceFactory $restrictionsFactory,
        EngineMetadataPool $engineMetadataPool,
        CoreRestrictionsProvider $coreRestrictionsProvider,
        $restrictions = []
    ) {
        $this->restrictionsFactory = $restrictionsFactory;
        $this->engineMetadataPool = $engineMetadataPool;
        $this->coreRestrictionsProvider = $coreRestrictionsProvider;
        $this->restrictions = $restrictions;
    }

    /**
     * Retrieves restrictions instance
     *
     * @param string $engineCode
     * @return RestrictionsInterface
     * @throws \Exception
     */
    public function getRestrictions($engineCode)
    {
        if (!isset($this->restrictionsInstances[$engineCode])) {
            $metadata = $this->engineMetadataPool->getMetadata($engineCode);
            $isGateway = $metadata->isGateway();

            if ($isGateway && !isset($this->restrictions[$engineCode])) {
                throw new \Exception(sprintf('Unknown subscription engine: %s requested', $engineCode));
            }

            $engineRestrictions = isset($this->restrictions[$engineCode])
                ? $this->restrictions[$engineCode]
                : null;
            /** @var Restrictions $coreRestrictions */
            $coreRestrictions = $this->coreRestrictionsProvider->getRestrictions();

            if ($isGateway) {
                $restrictionsData = $engineRestrictions;
            } else {
                $restrictionsData = $coreRestrictions->getData();
                if ($engineRestrictions) {
                    $restrictionsData = array_merge($restrictionsData, $engineRestrictions);
                }
            }
            $this->restrictionsInstances[$engineCode] = $this->restrictionsFactory->create(
                ['data' => $restrictionsData]
            );
        }
        return $this->restrictionsInstances[$engineCode];
    }
}
