<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine;

/**
 * Class EngineMetadata
 * @package Aheadworks\Sarp\Model\SubscriptionEngine
 */
class EngineMetadata extends \Magento\Framework\DataObject implements EngineMetadataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const CODE = 'code';
    const IS_GATEWAY = 'is_gateway';
    const CLASS_NAME = 'class_name';
    const LABEL = 'label';
    const PAYMENT_METHOD = 'payment_method';
    const PLAN_VALIDATOR_CLASS_NAME = 'plan_validator_class_name';
    const CHECKOUT_CONFIG_CLASS_NAME = 'checkout_config_class_name';
    const DATA_SOURCES = 'data_sources';
    const DATA_SOURCE_MAPS = 'data_source_maps';
    const PAYMENT_METHODS = 'payment_methods';
    const REQUIRED_MODULES = 'required_modules';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function isGateway()
    {
        return $this->getData(self::IS_GATEWAY);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->getData(self::CLASS_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethod()
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethods()
    {
        return $this->getData(self::PAYMENT_METHODS) ? : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPlanValidatorClassName()
    {
        return $this->getData(self::PLAN_VALIDATOR_CLASS_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getCheckoutConfigClassName()
    {
        return $this->getData(self::CHECKOUT_CONFIG_CLASS_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSources()
    {
        return $this->getData(self::DATA_SOURCES);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSourceMaps()
    {
        return $this->getData(self::DATA_SOURCE_MAPS);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredModules()
    {
        return $this->getData(self::REQUIRED_MODULES) ? : [];
    }
}
