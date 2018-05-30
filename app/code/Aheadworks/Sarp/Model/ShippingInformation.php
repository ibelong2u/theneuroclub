<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\ShippingInformationExtensionInterface;
use Aheadworks\Sarp\Api\Data\ShippingInformationInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class ShippingInformation
 * @package Aheadworks\Sarp\Model
 */
class ShippingInformation extends AbstractExtensibleObject implements ShippingInformationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getShippingAddress()
    {
        return $this->_get(self::SHIPPING_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingAddress(SubscriptionsCartAddressInterface $address)
    {
        return $this->setData(self::SHIPPING_ADDRESS, $address);
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress()
    {
        return $this->_get(self::BILLING_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingAddress(SubscriptionsCartAddressInterface $address)
    {
        return $this->setData(self::BILLING_ADDRESS, $address);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingMethodCode()
    {
        return $this->_get(self::SHIPPING_METHOD_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingMethodCode($code)
    {
        return $this->setData(self::SHIPPING_METHOD_CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingCarrierCode()
    {
        return $this->_get(self::SHIPPING_CARRIER_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingCarrierCode($code)
    {
        return $this->setData(self::SHIPPING_CARRIER_CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(ShippingInformationExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
