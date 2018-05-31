<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface SubscriptionsCartItemInterface
 * @package Aheadworks\Sarp\Api\Data
 */
interface SubscriptionsCartItemInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const ITEM_ID = 'item_id';
    const CART_ID = 'cart_id';
    const PARENT_ITEM_ID = 'parent_item_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const QTY = 'qty';
    const PRODUCT_ID = 'product_id';
    const NAME = 'name';
    const SKU = 'sku';
    const BUY_REQUEST = 'buy_request';
    const PRODUCT_OPTIONS = 'product_options';
    const REGULAR_PRICE = 'regular_price';
    const BASE_REGULAR_PRICE = 'base_regular_price';
    const REGULAR_PRICE_INCL_TAX = 'regular_price_incl_tax';
    const BASE_REGULAR_PRICE_INCL_TAX = 'base_regular_price_incl_tax';
    const TRIAL_PRICE = 'trial_price';
    const BASE_TRIAL_PRICE = 'base_trial_price';
    const TRIAL_PRICE_INCL_TAX = 'trial_price_incl_tax';
    const BASE_TRIAL_PRICE_INCL_TAX = 'base_trial_price_incl_tax';
    const INITIAL_FEE = 'initial_fee';
    const BASE_INITIAL_FEE = 'base_initial_fee';
    const IS_DELETED = 'is_deleted';
    const ROW_WEIGHT = 'row_weight';
    const ROW_TOTAL = 'row_total';
    const BASE_ROW_TOTAL = 'base_row_total';
    const ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';
    const BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';
    const TAX_PERCENT = 'tax_percent';
    const TAX_AMOUNT = 'tax_amount';
    const BASE_TAX_AMOUNT = 'base_tax_amount';
    const TRIAL_ROW_TOTAL = 'trial_row_total';
    const BASE_TRIAL_ROW_TOTAL = 'base_trial_row_total';
    const TRIAL_ROW_TOTAL_INCL_TAX = 'trial_row_total_incl_tax';
    const BASE_TRIAL_ROW_TOTAL_INCL_TAX = 'base_trial_row_total_incl_tax';
    const TRIAL_TAX_PERCENT = 'trial_tax_percent';
    const TRIAL_TAX_AMOUNT = 'trial_tax_amount';
    const BASE_TRIAL_TAX_AMOUNT = 'base_trial_tax_amount';
    const TAX_CALCULATION_ITEM_ID = 'tax_calculation_item_id';
    /**#@-*/

    /**
     * Get cart item ID
     *
     * @return int|null
     */
    public function getItemId();

    /**
     * Set cart item ID
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * Get cart ID
     *
     * @return int
     */
    public function getCartId();

    /**
     * Set cart ID
     *
     * @param int $cartId
     * @return $this
     */
    public function setCartId($cartId);

    /**
     * Get parent item ID
     *
     * @return int|null
     */
    public function getParentItemId();

    /**
     * Set parent item ID
     *
     * @param int $parentItemId
     * @return $this
     */
    public function setParentItemId($parentItemId);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get product SKU
     *
     * @return string
     */
    public function getSku();

    /**
     * Set product SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set creation time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set update time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get the product quantity
     *
     * @return float
     */
    public function getQty();

    /**
     * Set the product quantity
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Get product ID
     *
     * @return int
     */
    public function getProductId();

    /**
     * Set product ID
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Get buy request
     *
     * @return string
     */
    public function getBuyRequest();

    /**
     * Set buy request
     *
     * @param string $buyRequest
     * @return $this
     */
    public function setBuyRequest($buyRequest);

    /**
     * Get product options
     *
     * @return string
     */
    public function getProductOptions();

    /**
     * Set product options
     *
     * @param string $productOptions
     * @return $this
     */
    public function setProductOptions($productOptions);

    /**
     * Get regular price in cart currency
     *
     * @return float
     */
    public function getRegularPrice();

    /**
     * Set regular price in cart currency
     *
     * @param float $regularPrice
     * @return $this
     */
    public function setRegularPrice($regularPrice);

    /**
     * Get regular price in base currency
     *
     * @return float
     */
    public function getBaseRegularPrice();

    /**
     * Set regular price in base currency
     *
     * @param float $baseRegularPrice
     * @return $this
     */
    public function setBaseRegularPrice($baseRegularPrice);

    /**
     * Get regular price including tax in cart currency
     *
     * @return float
     */
    public function getRegularPriceInclTax();

    /**
     * Set regular price including tax in cart currency
     *
     * @param float $regularPriceInclTax
     * @return $this
     */
    public function setRegularPriceInclTax($regularPriceInclTax);

    /**
     * Get regular price including tax in base currency
     *
     * @return float
     */
    public function getBaseRegularPriceInclTax();

    /**
     * Set regular price including tax in base currency
     *
     * @param float $baseRegularPriceInclTax
     * @return $this
     */
    public function setBaseRegularPriceInclTax($baseRegularPriceInclTax);

    /**
     * Get trial price in cart currency
     *
     * @return float|null
     */
    public function getTrialPrice();

    /**
     * Set trial price in cart currency
     *
     * @param float $trialPrice
     * @return $this
     */
    public function setTrialPrice($trialPrice);

    /**
     * Get trial price in base currency
     *
     * @return float|null
     */
    public function getBaseTrialPrice();

    /**
     * Set trial price in base currency
     *
     * @param float $baseTrialPrice
     * @return $this
     */
    public function setBaseTrialPrice($baseTrialPrice);

    /**
     * Get trial price including tax in cart currency
     *
     * @return float
     */
    public function getTrialPriceInclTax();

    /**
     * Set trial price including tax in cart currency
     *
     * @param float $trialPriceInclTax
     * @return $this
     */
    public function setTrialPriceInclTax($trialPriceInclTax);

    /**
     * Get trial price including tax in base currency
     *
     * @return float
     */
    public function getBaseTrialPriceInclTax();

    /**
     * Set trial price including tax in base currency
     *
     * @param float $baseTrialPriceInclTax
     * @return $this
     */
    public function setBaseTrialPriceInclTax($baseTrialPriceInclTax);

    /**
     * Get initial fee in cart currency
     *
     * @return float|null
     */
    public function getInitialFee();

    /**
     * Set initial fee in cart currency
     *
     * @param float $initialFee
     * @return $this
     */
    public function setInitialFee($initialFee);

    /**
     * Get initial fee in base currency
     *
     * @return float|null
     */
    public function getBaseInitialFee();

    /**
     * Set initial fee in base currency
     *
     * @param float $baseInitialFee
     * @return $this
     */
    public function setBaseInitialFee($baseInitialFee);

    /**
     * Check if item deleted
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDeleted();

    /**
     * Set is deleted flag
     *
     * @param bool $isDeleted
     * @return $this
     */
    public function setIsDeleted($isDeleted);

    /**
     * Get row weight
     *
     * @return float
     */
    public function getRowWeight();

    /**
     * Set row weight
     *
     * @param float $rowWeight
     * @return $this
     */
    public function setRowWeight($rowWeight);

    /**
     * Get row total in cart currency
     *
     * @return float
     */
    public function getRowTotal();

    /**
     * Set row total in cart currency
     *
     * @param float $rowTotal
     * @return $this
     */
    public function setRowTotal($rowTotal);

    /**
     * Get row total in base currency
     *
     * @return float
     */
    public function getBaseRowTotal();

    /**
     * Set row total in base currency
     *
     * @param float $baseRowTotal
     * @return $this
     */
    public function setBaseRowTotal($baseRowTotal);

    /**
     * Get row total including tax in cart currency
     *
     * @return float
     */
    public function getRowTotalInclTax();

    /**
     * Set row total including tax in cart currency
     *
     * @param float $rowTotalInclTax
     * @return $this
     */
    public function setRowTotalInclTax($rowTotalInclTax);

    /**
     * Get row total including tax in base currency
     *
     * @return float
     */
    public function getBaseRowTotalInclTax();

    /**
     * Set row total including tax in base currency
     *
     * @param float $baseRowTotalInclTax
     * @return $this
     */
    public function setBaseRowTotalInclTax($baseRowTotalInclTax);

    /**
     * Get tax percent
     *
     * @return float
     */
    public function getTaxPercent();

    /**
     * Set tax percent
     *
     * @param float $taxPercent
     * @return $this
     */
    public function setTaxPercent($taxPercent);

    /**
     * Get tax amount in cart currency
     *
     * @return float
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
     * Get trial row total in cart currency
     *
     * @return float
     */
    public function getTrialRowTotal();

    /**
     * Set trial row total in cart currency
     *
     * @param float $trialRowTotal
     * @return $this
     */
    public function setTrialRowTotal($trialRowTotal);

    /**
     * Get trial row total in base currency
     *
     * @return float
     */
    public function getBaseTrialRowTotal();

    /**
     * Set trial row total in base currency
     *
     * @param float $baseTrialRowTotal
     * @return $this
     */
    public function setBaseTrialRowTotal($baseTrialRowTotal);

    /**
     * Get trial row total including tax in cart currency
     *
     * @return float
     */
    public function getTrialRowTotalInclTax();

    /**
     * Set trial row total including tax in cart currency
     *
     * @param float $trialRowTotalInclTax
     * @return $this
     */
    public function setTrialRowTotalInclTax($trialRowTotalInclTax);

    /**
     * Get trial row total including tax in base currency
     *
     * @return float
     */
    public function getBaseTrialRowTotalInclTax();

    /**
     * Set trial row total including tax in base currency
     *
     * @param float $baseTrialRowTotalInclTax
     * @return $this
     */
    public function setBaseTrialRowTotalInclTax($baseTrialRowTotalInclTax);

    /**
     * Get trial tax percent
     *
     * @return float
     */
    public function getTrialTaxPercent();

    /**
     * Set trial tax percent
     *
     * @param float $trialTaxPercent
     * @return $this
     */
    public function setTrialTaxPercent($trialTaxPercent);

    /**
     * Get trial tax amount in cart currency
     *
     * @return float
     */
    public function getTrialTaxAmount();

    /**
     * Set trial tax amount in cart currency
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
     * Get tax calculation item Id
     *
     * @return int|null
     */
    public function getTaxCalculationItemId();

    /**
     * Set tax calculation item Id
     *
     * @param int $taxCalculationItemId
     * @return $this
     */
    public function setTaxCalculationItemId($taxCalculationItemId);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartItemExtensionInterface $extensionAttributes
    );
}
