<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine;

use Aheadworks\Sarp\Model\Checkout\ConfigProviderInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class CheckoutConfigFactory
 * @package Aheadworks\Sarp\Model\SubscriptionEngine
 */
class CheckoutConfigFactory
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
     * Create checkout config instance
     *
     * @param string $className
     * @return ConfigProviderInterface
     */
    public function create($className)
    {
        return $this->objectManager->create($className);
    }
}
