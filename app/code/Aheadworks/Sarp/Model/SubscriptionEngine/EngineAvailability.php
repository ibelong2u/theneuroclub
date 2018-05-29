<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine;

use Magento\Framework\Module\Manager as ModuleManager;

/**
 * Class EngineAvailability
 * @package Aheadworks\Sarp\Model\SubscriptionEngine
 */
class EngineAvailability
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @param ModuleManager $moduleManager
     */
    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Check if engine available
     *
     * @param EngineMetadataInterface $metadata
     * @return bool
     */
    public function isAvailable($metadata)
    {
        foreach ($metadata->getRequiredModules() as $module) {
            if (!$this->moduleManager->isOutputEnabled($module)) {
                return false;
            }
        }
        return true;
    }
}
