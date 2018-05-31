<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class Totals
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 */
class Totals extends AbstractExtensibleObject implements SubscriptionsCartTotalsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getGrandTotal()
    {
        return $this->_get(self::GRAND_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setGrandTotal($grandTotal)
    {
        return $this->setData(self::GRAND_TOTAL, $grandTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseGrandTotal()
    {
        return $this->_get(self::BASE_GRAND_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseGrandTotal($baseGrandTotal)
    {
        return $this->setData(self::BASE_GRAND_TOTAL, $baseGrandTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubtotal()
    {
        return $this->_get(self::SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubtotal($subtotal)
    {
        return $this->setData(self::SUBTOTAL, $subtotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseSubtotal()
    {
        return $this->_get(self::BASE_SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseSubtotal($baseSubtotal)
    {
        return $this->setData(self::BASE_SUBTOTAL, $baseSubtotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAmount()
    {
        return $this->_get(self::SHIPPING_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingAmount($shippingAmount)
    {
        return $this->setData(self::SHIPPING_AMOUNT, $shippingAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseShippingAmount()
    {
        return $this->_get(self::BASE_SHIPPING_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingAmount($baseShippingAmount)
    {
        return $this->setData(self::BASE_SHIPPING_AMOUNT, $baseShippingAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxAmount()
    {
        return $this->_get(self::TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setTaxAmount($taxAmount)
    {
        return $this->setData(self::TAX_AMOUNT, $taxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTaxAmount()
    {
        return $this->_get(self::BASE_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTaxAmount($baseTaxAmount)
    {
        return $this->setData(self::BASE_TAX_AMOUNT, $baseTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialSubtotal()
    {
        return $this->_get(self::TRIAL_SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialSubtotal($trialSubtotal)
    {
        return $this->setData(self::TRIAL_SUBTOTAL, $trialSubtotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialSubtotal()
    {
        return $this->_get(self::BASE_TRIAL_SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialSubtotal($baseTrialSubtotal)
    {
        return $this->setData(self::BASE_TRIAL_SUBTOTAL, $baseTrialSubtotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialTaxAmount()
    {
        return $this->_get(self::TRIAL_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialTaxAmount($trialTaxAmount)
    {
        return $this->setData(self::TRIAL_TAX_AMOUNT, $trialTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialTaxAmount()
    {
        return $this->_get(self::BASE_TRIAL_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialTaxAmount($baseTrialTaxAmount)
    {
        return $this->setData(self::BASE_TRIAL_TAX_AMOUNT, $baseTrialTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialFee()
    {
        return $this->_get(self::INITIAL_FEE);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialFee($initialFee)
    {
        return $this->setData(self::INITIAL_FEE, $initialFee);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialFee()
    {
        return $this->_get(self::BASE_INITIAL_FEE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialFee($baseInitialFee)
    {
        return $this->setData(self::BASE_INITIAL_FEE, $baseInitialFee);
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
    public function setExtensionAttributes(SubscriptionsCartTotalsExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
