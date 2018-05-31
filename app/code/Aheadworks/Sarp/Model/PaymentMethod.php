<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model;

use Aheadworks\Sarp\Api\Data\PaymentMethodInterface;
use Aheadworks\Sarp\Api\Data\PaymentMethodExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class PaymentMethod
 * @package Aheadworks\Sarp\Model
 */
class PaymentMethod extends AbstractExtensibleObject implements PaymentMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->_get(self::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
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
    public function setExtensionAttributes(PaymentMethodExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
