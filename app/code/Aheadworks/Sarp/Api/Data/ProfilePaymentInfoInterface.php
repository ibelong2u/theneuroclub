<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface ProfilePaymentInfoInterface
 * @package Aheadworks\Sarp\Api\Data
 */
interface ProfilePaymentInfoInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const PAYMENT_TYPE = 'payment_type';
    const TRANSACTION_ID = 'transaction_id';
    const AMOUNT = 'amount';
    const BASE_AMOUNT = 'base_amount';
    const TAX_AMOUNT = 'tax_amount';
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    const SHIPPING_AMOUNT = 'shipping_amount';
    const BASE_SHIPPING_AMOUNT = 'base_shipping_amount';
    const GRAND_TOTAL = 'grand_total';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const CURRENCY_CODE = 'currency_code';
    const BASE_CURRENCY_CODE = 'base_currency_code';
    /**#@-*/

    /**
     * Get payment type
     *
     * @return string
     */
    public function getPaymentType();

    /**
     * Set payment type
     *
     * @param string $paymentType
     * @return $this
     */
    public function setPaymentType($paymentType);

    /**
     * Get transaction ID
     *
     * @return string
     */
    public function getTransactionId();

    /**
     * Set transaction ID
     *
     * @param string $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId);

    /**
     * Get amount in profile currency
     *
     * @return float
     */
    public function getAmount();

    /**
     * Set amount in profile currency
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Get amount in base currency
     *
     * @return float
     */
    public function getBaseAmount();

    /**
     * Set amount in base currency
     *
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseAmount($baseAmount);

    /**
     * Get tax amount in profile currency
     *
     * @return float
     */
    public function getTaxAmount();

    /**
     * Set tax amount in profile currency
     *
     * @param float $taxAmount
     * @return $this
     */
    public function setTaxAmount($taxAmount);

    /**
     * Get tax amount in base currency
     *
     * @return float
     */
    public function getBaseTaxAmount();

    /**
     * Set tax amount in base currency
     *
     * @param float $baseTaxAmount
     * @return $this
     */
    public function setBaseTaxAmount($baseTaxAmount);

    /**
     * Get shipping amount in profile currency
     *
     * @return float
     */
    public function getShippingAmount();

    /**
     * Set shipping amount in profile currency
     *
     * @param float $shippingAmount
     * @return $this
     */
    public function setShippingAmount($shippingAmount);

    /**
     * Get shipping amount in base currency
     *
     * @return float
     */
    public function getBaseShippingAmount();

    /**
     * Set shipping amount in base currency
     *
     * @param float $baseShippingAmount
     * @return $this
     */
    public function setBaseShippingAmount($baseShippingAmount);

    /**
     * Get grand total in profile currency
     *
     * @return float
     */
    public function getGrandTotal();

    /**
     * Set grand total in profile currency
     *
     * @param float $grandTotal
     * @return $this
     */
    public function setGrandTotal($grandTotal);

    /**
     * Get grand total in base currency
     *
     * @return float
     */
    public function getBaseGrandTotal();

    /**
     * Set grand total in base currency
     *
     * @param float $baseGrandTotal
     * @return $this
     */
    public function setBaseGrandTotal($baseGrandTotal);

    /**
     * Get profile currency code
     *
     * @return string
     */
    public function getCurrencyCode();

    /**
     * Set profile currency code
     *
     * @param string $currencyCode
     * @return $this
     */
    public function setCurrencyCode($currencyCode);

    /**
     * Get base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode();

    /**
     * Set base currency code
     *
     * @param string $baseCurrencyCode
     * @return $this
     */
    public function setBaseCurrencyCode($baseCurrencyCode);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp\Api\Data\ProfilePaymentInfoExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp\Api\Data\ProfilePaymentInfoExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp\Api\Data\ProfilePaymentInfoExtensionInterface $extensionAttributes
    );
}
