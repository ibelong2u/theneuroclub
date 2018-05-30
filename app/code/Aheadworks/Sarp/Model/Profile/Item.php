<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Profile;

use Aheadworks\Sarp\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp\Api\Data\ProfileItemExtensionInterface;
use Aheadworks\Sarp\Model\ResourceModel\Profile\Item as ItemResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Item
 * @package Aheadworks\Sarp\Model\Profile
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Item extends AbstractModel implements ProfileItemInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ItemResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProfileId($profileId)
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentItemId()
    {
        return $this->getData(self::PARENT_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentItemId($parentItemId)
    {
        return $this->setData(self::PARENT_ITEM_ID, $parentItemId);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildItems()
    {
        return $this->getData(self::CHILD_ITEMS);
    }

    /**
     * {@inheritdoc}
     */
    public function setChildItems($childItems)
    {
        return $this->setData(self::CHILD_ITEMS, $childItems);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * {@inheritdoc}
     */
    public function getBuyRequest()
    {
        return $this->getData(self::BUY_REQUEST);
    }

    /**
     * {@inheritdoc}
     */
    public function setBuyRequest($buyRequest)
    {
        return $this->setData(self::BUY_REQUEST, $buyRequest);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductOptions()
    {
        return $this->getData(self::PRODUCT_OPTIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductOptions($productOptions)
    {
        return $this->setData(self::PRODUCT_OPTIONS, $productOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularPrice()
    {
        return $this->getData(self::REGULAR_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularPrice($regularPrice)
    {
        return $this->setData(self::REGULAR_PRICE, $regularPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularPrice()
    {
        return $this->getData(self::BASE_REGULAR_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularPrice($baseRegularPrice)
    {
        return $this->setData(self::BASE_REGULAR_PRICE, $baseRegularPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularPriceInclTax()
    {
        return $this->getData(self::REGULAR_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularPriceInclTax($regularPriceInclTax)
    {
        return $this->setData(self::REGULAR_PRICE_INCL_TAX, $regularPriceInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularPriceInclTax()
    {
        return $this->getData(self::BASE_REGULAR_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularPriceInclTax($baseRegularPriceInclTax)
    {
        return $this->setData(self::BASE_REGULAR_PRICE_INCL_TAX, $baseRegularPriceInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialPrice()
    {
        return $this->getData(self::TRIAL_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialPrice($trialPrice)
    {
        return $this->setData(self::TRIAL_PRICE, $trialPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialPrice()
    {
        return $this->getData(self::BASE_TRIAL_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialPrice($baseTrialPrice)
    {
        return $this->setData(self::BASE_TRIAL_PRICE, $baseTrialPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialPriceInclTax()
    {
        return $this->getData(self::TRIAL_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialPriceInclTax($trialPriceInclTax)
    {
        return $this->setData(self::TRIAL_PRICE_INCL_TAX, $trialPriceInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialPriceInclTax()
    {
        return $this->getData(self::BASE_TRIAL_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialPriceInclTax($baseTrialPriceInclTax)
    {
        return $this->setData(self::BASE_TRIAL_PRICE_INCL_TAX, $baseTrialPriceInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialFee()
    {
        return $this->getData(self::INITIAL_FEE);
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
        return $this->getData(self::BASE_INITIAL_FEE);
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
    public function getRowWeight()
    {
        return $this->getData(self::ROW_WEIGHT);
    }

    /**
     * {@inheritdoc}
     */
    public function setRowWeight($rowWeight)
    {
        return $this->setData(self::ROW_WEIGHT, $rowWeight);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowTotal()
    {
        return $this->getData(self::ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setRowTotal($rowTotal)
    {
        return $this->setData(self::ROW_TOTAL, $rowTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRowTotal()
    {
        return $this->getData(self::BASE_ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRowTotal($baseRowTotal)
    {
        return $this->setData(self::BASE_ROW_TOTAL, $baseRowTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowTotalInclTax()
    {
        return $this->getData(self::ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setRowTotalInclTax($rowTotalInclTax)
    {
        return $this->setData(self::ROW_TOTAL_INCL_TAX, $rowTotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRowTotalInclTax()
    {
        return $this->getData(self::BASE_ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRowTotalInclTax($baseRowTotalInclTax)
    {
        return $this->setData(self::BASE_ROW_TOTAL_INCL_TAX, $baseRowTotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxPercent()
    {
        return $this->getData(self::TAX_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setTaxPercent($taxPercent)
    {
        return $this->setData(self::TAX_PERCENT, $taxPercent);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxAmount()
    {
        return $this->getData(self::TAX_AMOUNT);
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
        return $this->getData(self::BASE_TAX_AMOUNT);
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
    public function getTrialRowTotal()
    {
        return $this->getData(self::TRIAL_ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialRowTotal($trialRowTotal)
    {
        return $this->setData(self::TRIAL_ROW_TOTAL, $trialRowTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialRowTotal()
    {
        return $this->getData(self::BASE_TRIAL_ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialRowTotal($baseTrialRowTotal)
    {
        return $this->setData(self::BASE_TRIAL_ROW_TOTAL, $baseTrialRowTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialRowTotalInclTax()
    {
        return $this->getData(self::TRIAL_ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialRowTotalInclTax($trialRowTotalInclTax)
    {
        return $this->setData(self::TRIAL_ROW_TOTAL_INCL_TAX, $trialRowTotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialRowTotalInclTax()
    {
        return $this->getData(self::BASE_TRIAL_ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialRowTotalInclTax($baseTrialRowTotalInclTax)
    {
        return $this->setData(self::BASE_TRIAL_ROW_TOTAL_INCL_TAX, $baseTrialRowTotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialTaxPercent()
    {
        return $this->getData(self::TRIAL_TAX_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialTaxPercent($trialTaxPercent)
    {
        return $this->setData(self::TRIAL_TAX_PERCENT, $trialTaxPercent);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialTaxAmount()
    {
        return $this->getData(self::TRIAL_TAX_AMOUNT);
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
        return $this->getData(self::BASE_TRIAL_TAX_AMOUNT);
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
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(ProfileItemExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
