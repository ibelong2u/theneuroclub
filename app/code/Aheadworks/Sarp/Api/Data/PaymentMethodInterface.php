<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface PaymentMethodInterface
 * @package Aheadworks\Sarp\Api\Data
 */
interface PaymentMethodInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const CODE = 'code';
    const TITLE = 'title';
    /**#@-*/

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set payment method code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get payment method title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set payment method title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp\Api\Data\PaymentMethodExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp\Api\Data\PaymentMethodExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp\Api\Data\PaymentMethodExtensionInterface $extensionAttributes
    );
}
