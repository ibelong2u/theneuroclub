<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Api;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class CompositeMapper
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Api
 */
class CompositeMapper implements MapperInterface
{
    /**
     * @var array
     */
    private $mapperClasses = [];

    /**
     * @var MapperInterface[]
     */
    private $mappers;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $mapperClasses
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $mapperClasses = []
    ) {
        $this->objectManager = $objectManager;
        $this->mapperClasses = $mapperClasses;
    }

    /**
     * {@inheritdoc}
     */
    public function toApi($method, $data)
    {
        $result = [];
        foreach ($this->getMappers() as $mapper) {
            $result = array_merge_recursive($result, $mapper->toApi($method, $data));
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function fromApi($method, $data)
    {
        $result = [];
        foreach ($this->getMappers() as $mapper) {
            $result = array_merge($result, $mapper->fromApi($method, $data));
        }
        return $result;
    }

    /**
     * Retrieve mappers instances
     *
     * @return MapperInterface[]
     * @throws \Exception
     */
    private function getMappers()
    {
        if (!$this->mappers) {
            foreach ($this->mapperClasses as $className) {
                $mapperInstance = $this->objectManager->create($className);
                if (!$mapperInstance instanceof MapperInterface) {
                    throw new \Exception(
                        sprintf('Mapper class %s does not implement required interface.', $className)
                    );
                }
                $this->mappers[] = $mapperInstance;
            }
        }
        return $this->mappers;
    }
}
