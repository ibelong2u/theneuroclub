<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Checkout\ConfigProvider;

use Aheadworks\Sarp\Model\Checkout\ConfigProviderInterface;
use Magento\Directory\Model\Country\Postcode\ConfigInterface as PostcodeConfig;
use Magento\Shipping\Model\Config as ShippingConfig;

/**
 * Class Shipping
 * @package Aheadworks\Sarp\Model\Checkout\ConfigProvider
 */
class Shipping implements ConfigProviderInterface
{
    /**
     * @var ShippingConfig
     */
    private $shippingConfig;

    /**
     * @var PostcodeConfig
     */
    private $postcodeConfig;

    /**
     * @param ShippingConfig $shippingConfig
     * @param PostcodeConfig $postcodeConfig
     */
    public function __construct(
        ShippingConfig $shippingConfig,
        PostcodeConfig $postcodeConfig
    ) {
        $this->shippingConfig = $shippingConfig;
        $this->postcodeConfig = $postcodeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'activeCarriers' => $this->getActiveCarriers(),
            'postCodes' => $this->postcodeConfig->getPostCodes()
        ];
    }

    /**
     * Get active carrier codes
     *
     * @return array
     */
    private function getActiveCarriers()
    {
        $activeCarriers = [];
        foreach ($this->shippingConfig->getActiveCarriers() as $carrier) {
            $activeCarriers[] = $carrier->getCarrierCode();
        }
        return $activeCarriers;
    }
}
