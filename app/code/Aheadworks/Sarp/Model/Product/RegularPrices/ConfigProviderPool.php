<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Product\RegularPrices;

use Aheadworks\Sarp\Model\Product\RegularPrices\ConfigProvider\Bundle as BundleProvider;
use Aheadworks\Sarp\Model\Product\RegularPrices\ConfigProvider\DefaultProvider;
use Aheadworks\Sarp\Model\Product\RegularPrices\ConfigProvider\Configurable as ConfigurableProvider;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ConfigProviderPool
 * @package Aheadworks\Sarp\Model\Product\RegularPrices
 */
class ConfigProviderPool
{
    /**
     * @var array
     */
    private $configProviders = [
        'bundle' => BundleProvider::class,
        'default' => DefaultProvider::class,
        'configurable' => ConfigurableProvider::class
    ];

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
     * Get config provider for specific product type
     *
     * @param string $typeId
     * @return ConfigProviderInterface
     * @throws \Exception
     */
    public function get($typeId)
    {
        $providerClassName = isset($this->configProviders[$typeId])
            ? $this->configProviders[$typeId]
            : $this->configProviders['default'];
        return $this->objectManager->get($providerClassName);
    }
}
