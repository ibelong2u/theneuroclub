<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Template;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class FormatterFactory
 * @package Aheadworks\Sarp\Model\Logger\Data\Template
 */
class FormatterFactory
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
     * Create formatter instance
     *
     * @param string $className
     * @return FormatterInterface
     */
    public function create($className)
    {
        return $this->objectManager->create($className);
    }
}
