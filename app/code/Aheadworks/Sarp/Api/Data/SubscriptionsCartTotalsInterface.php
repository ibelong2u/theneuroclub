<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface SubscriptionsCartTotalsInterface
 * @package Aheadworks\Sarp\Api\Data
 */
interface SubscriptionsCartTotalsInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const GRAND_TOTAL = 'grand_total';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const SUBTOTAL = 'subtotal';
    const BASE_SUBTOTAL = 'base_subtotal';
    const SHIPPING_AMOUNT = 'shipping_amount';
    const BASE_SHIPPING_AMOUNT = 'base_shipping_amount';
    const TAX_AMOUNT = 'tax_amount';
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    const TRIAL_SUBTOTAL = 'trial_subtotal';
    const BASE_TRIAL_SUBTOTAL = 'base_trial_subtotal';
    const TRIAL_TAX_AMOUNT = 'trial_tax_amount';
    const BASE_TRIAL_TAX_AMOUNT = 'base_trial_tax_amount';
    const INITIAL_FEE = 'initial_fee';
    const BASE_INITIAL_FEE = 'base_initial_fee';
    /**#@-*/

    /**
     * Get grand total in cart currency
     *
     * @return float|null
     */
    public function getGrandTotal();

    /**
     * Set grand total in cart currency
     *
     * @param float $grandTotal
     * @return $this
     */
    public function setGrandTotal($grandTotal);

    /**
     * Get grand total in base currency
     *
     * @return float|null
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
     * Get subtotal in cart currency
     *
     * @return float|null
     */
    public function getSubtotal();

    /**
     * Set subtotal in cart currency
     *
     * @param float $subtotal
     * @return $this
     */
    public function setSubtotal($subtotal);

    /**
     * Get subtotal in base currency
     *
     * @return float|null
     */
    public function getBaseSubtotal();

    /**
     * Set subtotal in base currency
     *
     * @param float $baseSubtotal
     * @return $this
     */
    public function setBaseSubtotal($baseSubtotal);

    /**
     * Get shipping amount in cart currency
     *
     * @return float|null
     */
    public function getShippingAmount();

    /**
     * Set shipping amount in cart currency
     *
     * @param float $shippingAmount
     * @return $this
     */
    public function setShippingAmount($shippingAmount);

    /**
     * Get shipping amount in base currency
     *
     * @return float|null
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
     * Get tax amount in cart currency
     *
     * @return float|null
     */
    public function getTaxAmount();

    /**
     * Set tax amount in cart currency
     *
     * @param float $taxAmount
     * @return $this
     */
    public function setTaxAmount($taxAmount);

    /**
     * Get tax amount in base currency
     *
     * @return float|null
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
     * Get trial subtotal
     *
     * @return float
     */
    public function getTrialSubtotal();

    /**
     * Set trial subtotal
     *
     * @param float $trialSubtotal
     * @return $this
     */
    public function setTrialSubtotal($trialSubtotal);

    /**
     * Get trial subtotal in base currency
     *
     * @return float
     */
    public function getBaseTrialSubtotal();

    /**
     * Set trial subtotal in base currency
     *
     * @param float $baseTrialSubtotal
     * @return $this
     */
    public function setBaseTrialSubtotal($baseTrialSubtotal);

    /**
     * Get trial tax amount
     *
     * @return float
     */
    public function getTrialTaxAmount();

    /**
     * Set trial tax amount
     *
     * @param float $trialTaxAmount
     * @return $this
     */
    public function setTrialTaxAmount($trialTaxAmount);

    /**
     * Get trial tax amount in base currency
     *
     * @return float
     */
    public function getBaseTrialTaxAmount();

    /**
     * Set trial tax amount in base currency
     *
     * @param float $baseTrialTaxAmount
     * @return $this
     */
    public function setBaseTrialTaxAmount($baseTrialTaxAmount);

    /**
     * Get initial fee amount
     *
     * @return float
     */
    public function getInitialFee();

    /**
     * Set initial fee amount
     *
     * @param float $initialFee
     * @return $this
     */
    public function setInitialFee($initialFee);

    /**
     * Get initial fee amount in base currency
     *
     * @return float
     */
    public function getBaseInitialFee();

    /**
     * Set initial fee amount in base currency
     *
     * @param float $baseInitialFee
     * @return $this
     */
    public function setBaseInitialFee($baseInitialFee);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsExtensionInterface $extensionAttributes
    );
}
