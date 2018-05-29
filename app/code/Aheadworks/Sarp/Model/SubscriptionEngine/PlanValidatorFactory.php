<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class PlanValidatorFactory
 * @package Aheadworks\Sarp\Model\SubscriptionEngine
 */
class PlanValidatorFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create subscription plan validator instance
     *
     * @param string $className
     * @return \Zend_Validate_Interface
     */
    public function create($className)
    {
        return $this->objectManager->create($className);
    }
}
