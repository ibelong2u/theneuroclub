<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartExtensionInterface;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart as SubscriptionsCartResource;
use Aheadworks\Sarp\Model\SubscriptionsCart\Validator as SubscriptionsCartValidator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SubscriptionsCart
 * @package Aheadworks\Sarp\Model
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SubscriptionsCart extends AbstractModel implements SubscriptionsCartInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SubscriptionsCartValidator
     */
    private $validator;

    /**
     * @var ProductRepositoryInterface;
     */
    private $productRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param SubscriptionsCartValidator $validator
     * @param ProductRepositoryInterface $productRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        SubscriptionsCartValidator $validator,
        ProductRepositoryInterface $productRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->validator = $validator;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(SubscriptionsCartResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getCartId()
    {
        return $this->getData(self::CART_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCartId($cartId)
    {
        return $this->setData(self::CART_ID, $cartId);
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
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->getData(self::ITEMS) ? : [];
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
    public function getIsVirtual()
    {
        return $this->getData(self::IS_VIRTUAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsVirtual($isVirtual)
    {
        return $this->setData(self::IS_VIRTUAL, $isVirtual);
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
    public function setSubscriptionPlanId($subscriptionPlanId)
    {
        return $this->setData(self::SUBSCRIPTION_PLAN_ID, $subscriptionPlanId);
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
    public function getCustomer()
    {
        return $this->getData(self::CUSTOMER);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomer($customer)
    {
        return $this->setData(self::CUSTOMER, $customer);
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
    public function getCartCurrencyCode()
    {
        return $this->getData(self::CART_CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCartCurrencyCode($cartCurrencyCode)
    {
        return $this->setData(self::CART_CURRENCY_CODE, $cartCurrencyCode);
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
    public function getBaseToCartRate()
    {
        return $this->getData(self::BASE_TO_CART_RATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseToCartRate($baseToCartRate)
    {
        return $this->setData(self::BASE_TO_CART_RATE, $baseToCartRate);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddresses()
    {
        return $this->getData(self::ADDRESSES) ? : [];
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
    public function setExtensionAttributes(SubscriptionsCartExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        /** @var \Magento\Store\Api\Data\StoreInterface|\Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore($this->getStoreId());

        $globalCurrencyCode = $this->scopeConfig->getValue(Currency::XML_PATH_CURRENCY_BASE, 'default');
        $baseCurrency = $store->getBaseCurrency();
        $cartCurrency = $store->getCurrentCurrency();

        $this
            ->setGlobalCurrencyCode($globalCurrencyCode)
            ->setBaseCurrencyCode($baseCurrency->getCode())
            ->setCartCurrencyCode($cartCurrency->getCode())
            ->setBaseToGlobalRate($baseCurrency->getRate($globalCurrencyCode))
            ->setBaseToCartRate($baseCurrency->getRate($cartCurrency));
        $this->setIsVirtual($this->isVirtual());

        parent::beforeSave();
    }

    /**
     * Check if cart virtual
     *
     * @return bool
     */
    private function isVirtual()
    {
        $isVirtual = true;
        foreach ($this->getItems() as $item) {
            if (!$item->getIsDeleted()) {
                $product = $this->productRepository->getById($item->getProductId());
                if (!$product->getIsVirtual()) {
                    $isVirtual = false;
                    break;
                }
            }
        }
        return $isVirtual;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }
}
