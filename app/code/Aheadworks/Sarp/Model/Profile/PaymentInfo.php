<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Profile;

use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class PaymentInfo
 * @package Aheadworks\Sarp\Model\Profile
 */
class PaymentInfo extends AbstractExtensibleObject implements ProfilePaymentInfoInterface
{
    /**
     * Payment type constants
     */
    const PAYMENT_TYPE_REGULAR = 'regular';
    const PAYMENT_TYPE_INITIAL = 'initial';
    const PAYMENT_TYPE_TRIAL = 'trial';

    /**
     * {@inheritdoc}
     */
    public function getPaymentType()
    {
        return $this->_get(self::PAYMENT_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentType($paymentType)
    {
        return $this->setData(self::PAYMENT_TYPE, $paymentType);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionId()
    {
        return $this->_get(self::TRANSACTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        return $this->_get(self::AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAmount()
    {
        return $this->_get(self::BASE_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmount($baseAmount)
    {
        return $this->setData(self::BASE_AMOUNT, $baseAmount);
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
    public function getCurrencyCode()
    {
        return $this->_get(self::CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencyCode($currencyCode)
    {
        return $this->setData(self::CURRENCY_CODE, $currencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCurrencyCode()
    {
        return $this->_get(self::BASE_CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseCurrencyCode($baseCurrencyCode)
    {
        return $this->setData(self::BASE_CURRENCY_CODE, $baseCurrencyCode);
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
    public function setExtensionAttributes(ProfilePaymentInfoExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
