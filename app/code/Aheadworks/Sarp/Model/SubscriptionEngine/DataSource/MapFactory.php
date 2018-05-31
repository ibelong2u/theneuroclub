<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\DataSource;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class MapFactory
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\DataSource
 */
class MapFactory
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
     * Create map instance
     *
     * @param string $className
     * @return MapInterface
     */
    public function create($className)
    {
        return $this->objectManager->create($className);
    }
}
