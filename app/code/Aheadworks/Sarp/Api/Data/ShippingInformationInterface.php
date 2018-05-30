<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface ShippingInformationInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const SHIPPING_ADDRESS = 'shipping_address';
    const BILLING_ADDRESS = 'billing_address';
    const SHIPPING_METHOD_CODE = 'shipping_method_code';
    const SHIPPING_CARRIER_CODE = 'shipping_carrier_code';
    /**#@-*/

    /**
     * Returns shipping address
     *
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface
     */
    public function getShippingAddress();

    /**
     * Set shipping address
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $address
     * @return $this
     */
    public function setShippingAddress(SubscriptionsCartAddressInterface $address);

    /**
     * Returns billing address
     *
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface|null
     */
    public function getBillingAddress();

    /**
     * Set billing address if additional synchronization needed
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface $address
     * @return $this
     */
    public function setBillingAddress(SubscriptionsCartAddressInterface $address);

    /**
     * Returns shipping method code
     *
     * @return string
     */
    public function getShippingMethodCode();

    /**
     * Set shipping method code
     *
     * @param string $code
     * @return $this
     */
    public function setShippingMethodCode($code);

    /**
     * Returns carrier code
     *
     * @return string
     */
    public function getShippingCarrierCode();

    /**
     * Set carrier code
     *
     * @param string $code
     * @return $this
     */
    public function setShippingCarrierCode($code);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Aheadworks\Sarp\Api\Data\ShippingInformationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Aheadworks\Sarp\Api\Data\ShippingInformationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp\Api\Data\ShippingInformationExtensionInterface $extensionAttributes
    );
}
