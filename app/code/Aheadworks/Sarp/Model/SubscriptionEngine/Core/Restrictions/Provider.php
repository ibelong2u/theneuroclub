<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Restrictions;

use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\RestrictionsInterfaceFactory;

/**
 * Class Provider
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Restrictions
 */
class Provider
{
    /**
     * @var RestrictionsInterfaceFactory
     */
    private $restrictionsFactory;

    /**
     * @var array
     */
    private $restrictions = [];

    /**
     * @var RestrictionsInterface
     */
    private $instance;

    /**
     * @param RestrictionsInterfaceFactory $restrictionsFactory
     * @param array $restrictions
     */
    public function __construct(
        RestrictionsInterfaceFactory $restrictionsFactory,
        $restrictions = []
    ) {
        $this->restrictionsFactory = $restrictionsFactory;
        $this->restrictions = $restrictions;
    }

    /**
     * Get core restrictions instance
     *
     * @return RestrictionsInterface
     */
    public function getRestrictions()
    {
        if (!$this->instance) {
            $this->instance = $this->restrictionsFactory->create(['data' => $this->restrictions]);
        }
        return $this->instance;
    }
}
