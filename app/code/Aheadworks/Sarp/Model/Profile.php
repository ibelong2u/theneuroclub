<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfileExtensionInterface;
use Aheadworks\Sarp\Model\ResourceModel\Profile as ProfileResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Profile
 * @package Aheadworks\Sarp\Model
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Profile extends AbstractModel implements ProfileInterface
{
    /**
     * Entity type
     */
    const ENTITY = 'aw_sarp_profile';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ProfileResource::class);
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
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceId()
    {
        return $this->getData(self::REFERENCE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setReferenceId($referenceId)
    {
        return $this->setData(self::REFERENCE_ID, $referenceId);
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
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getEngineCode()
    {
        return $this->getData(self::ENGINE_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setEngineCode($engineCode)
    {
        return $this->setData(self::ENGINE_CODE, $engineCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethodCode()
    {
        return $this->getData(self::PAYMENT_METHOD_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentMethodCode($methodCode)
    {
        return $this->setData(self::PAYMENT_METHOD_CODE, $methodCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethodTitle()
    {
        return $this->getData(self::PAYMENT_METHOD_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentMethodTitle($methodTitle)
    {
        return $this->setData(self::PAYMENT_METHOD_TITLE, $methodTitle);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastOrderId()
    {
        return $this->getData(self::LAST_ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastOrderId($lastOrderId)
    {
        return $this->setData(self::LAST_ORDER_ID, $lastOrderId);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastOrderDate()
    {
        return $this->getData(self::LAST_ORDER_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastOrderDate($lastOrderDate)
    {
        return $this->setData(self::LAST_ORDER_DATE, $lastOrderDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptionPlanId()
    {
        return $this->getData(self::SUBSCRIPTION_PLAN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubscriptionPlanId($planId)
    {
        return $this->setData(self::SUBSCRIPTION_PLAN_ID, $planId);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptionPlanName()
    {
        return $this->getData(self::SUBSCRIPTION_PLAN_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubscriptionPlanName($subscriptionPlanName)
    {
        return $this->setData(self::SUBSCRIPTION_PLAN_NAME, $subscriptionPlanName);
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingPeriod()
    {
        return $this->getData(self::BILLING_PERIOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingPeriod($billingPeriod)
    {
        return $this->setData(self::BILLING_PERIOD, $billingPeriod);
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingFrequency()
    {
        return $this->getData(self::BILLING_FREQUENCY);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingFrequency($billingFrequency)
    {
        return $this->setData(self::BILLING_FREQUENCY, $billingFrequency);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalBillingCycles()
    {
        return $this->getData(self::TOTAL_BILLING_CYCLES);
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalBillingCycles($totalBillingCycles)
    {
        return $this->setData(self::TOTAL_BILLING_CYCLES, $totalBillingCycles);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsInitialFeeEnabled()
    {
        return $this->getData(self::IS_INITIAL_FEE_ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsInitialFeeEnabled($isInitialFeeEnabled)
    {
        return $this->setData(self::IS_INITIAL_FEE_ENABLED, $isInitialFeeEnabled);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsTrialPeriodEnabled()
    {
        return $this->getData(self::IS_TRIAL_PERIOD_ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsTrialPeriodEnabled($isTrialPeriodEnabled)
    {
        return $this->setData(self::IS_TRIAL_PERIOD_ENABLED, $isTrialPeriodEnabled);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialTotalBillingCycles()
    {
        return $this->getData(self::TRIAL_TOTAL_BILLING_CYCLES);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialTotalBillingCycles($trialTotalBillingCycles)
    {
        return $this->setData(self::TRIAL_TOTAL_BILLING_CYCLES, $trialTotalBillingCycles);
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDate()
    {
        return $this->getData(self::START_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsCartVirtual()
    {
        return $this->getData(self::IS_CART_VIRTUAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsCartVirtual($isCartVirtual)
    {
        return $this->setData(self::IS_CART_VIRTUAL, $isCartVirtual);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->getData(self::ITEMS);
    }

    /**
     * {@inheritdoc}
     */
    public function setItems($items)
    {
        return $this->setData(self::ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     */
    public function getInnerItems()
    {
        return $this->getData(self::INNER_ITEMS) ? : [];
    }

    /**
     * {@inheritdoc}
     */
    public function setInnerItems($innerItems)
    {
        return $this->setData(self::INNER_ITEMS, $innerItems);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddresses()
    {
        return $this->getData(self::ADDRESSES);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddresses($addresses)
    {
        return $this->setData(self::ADDRESSES, $addresses);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroupId()
    {
        return $this->getData(self::CUSTOMER_GROUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerGroupId($customerGroupId)
    {
        return $this->setData(self::CUSTOMER_GROUP_ID, $customerGroupId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerFullname()
    {
        return $this->getData(self::CUSTOMER_FULLNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerFullname($customerFullName)
    {
        return $this->setData(self::CUSTOMER_FULLNAME, $customerFullName);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerPrefix()
    {
        return $this->getData(self::CUSTOMER_PREFIX);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerPrefix($customerPrefix)
    {
        return $this->setData(self::CUSTOMER_PREFIX, $customerPrefix);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerFirstname()
    {
        return $this->getData(self::CUSTOMER_FIRSTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerFirstname($firstname)
    {
        return $this->setData(self::CUSTOMER_FIRSTNAME, $firstname);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerMiddlename()
    {
        return $this->getData(self::CUSTOMER_MIDDLENAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerMiddlename($middlename)
    {
        return $this->setData(self::CUSTOMER_MIDDLENAME, $middlename);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerLastname()
    {
        return $this->getData(self::CUSTOMER_LASTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerLastname($lastname)
    {
        return $this->setData(self::CUSTOMER_LASTNAME, $lastname);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerSuffix()
    {
        return $this->getData(self::CUSTOMER_SUFFIX);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerSuffix($customerSuffix)
    {
        return $this->setData(self::CUSTOMER_SUFFIX, $customerSuffix);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerDob()
    {
        return $this->getData(self::CUSTOMER_DOB);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerDob($customerDob)
    {
        return $this->setData(self::CUSTOMER_DOB, $customerDob);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerIsGuest()
    {
        return $this->getData(self::CUSTOMER_IS_GUEST);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerIsGuest($customerIsGuest)
    {
        return $this->setData(self::CUSTOMER_IS_GUEST, $customerIsGuest);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingMethod()
    {
        return $this->getData(self::SHIPPING_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingMethod($shippingMethod)
    {
        return $this->setData(self::SHIPPING_METHOD, $shippingMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingDescription()
    {
        return $this->getData(self::SHIPPING_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingDescription($shippingDescription)
    {
        return $this->setData(self::SHIPPING_DESCRIPTION, $shippingDescription);
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobalCurrencyCode()
    {
        return $this->getData(self::GLOBAL_CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setGlobalCurrencyCode($baseCurrencyCode)
    {
        return $this->setData(self::GLOBAL_CURRENCY_CODE, $baseCurrencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCurrencyCode()
    {
        return $this->getData(self::BASE_CURRENCY_CODE);
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
    public function getProfileCurrencyCode()
    {
        return $this->getData(self::PROFILE_CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setProfileCurrencyCode($profileCurrencyCode)
    {
        return $this->setData(self::PROFILE_CURRENCY_CODE, $profileCurrencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseToGlobalRate()
    {
        return $this->getData(self::BASE_TO_GLOBAL_RATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseToGlobalRate($baseToGlobalRate)
    {
        return $this->setData(self::BASE_TO_GLOBAL_RATE, $baseToGlobalRate);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseToProfileRate()
    {
        return $this->getData(self::BASE_TO_PROFILE_RATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseToProfileRate($baseToProfileRate)
    {
        return $this->setData(self::BASE_TO_PROFILE_RATE, $baseToProfileRate);
    }

    /**
     * {@inheritdoc}
     */
    public function getGrandTotal()
    {
        return $this->getData(self::GRAND_TOTAL);
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
        return $this->getData(self::BASE_GRAND_TOTAL);
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
        return $this->getData(self::SUBTOTAL);
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
        return $this->getData(self::BASE_SUBTOTAL);
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
        return $this->getData(self::SHIPPING_AMOUNT);
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
        return $this->getData(self::BASE_SHIPPING_AMOUNT);
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
    public function getTrialSubtotal()
    {
        return $this->getData(self::TRIAL_SUBTOTAL);
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
        return $this->getData(self::BASE_TRIAL_SUBTOTAL);
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
    public function getRemoteIp()
    {
        return $this->getData(self::REMOTE_IP);
    }

    /**
     * {@inheritdoc}
     */
    public function setRemoteIp($remoteIp)
    {
        return $this->setData(self::REMOTE_IP, $remoteIp);
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
    public function setExtensionAttributes(ProfileExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
