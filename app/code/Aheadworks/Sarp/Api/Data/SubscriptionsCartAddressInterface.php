<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface AddressInterface
 * @package Aheadworks\Sarp\Api\Data
 */
interface SubscriptionsCartAddressInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const ADDRESS_ID = 'address_id';
    const CART_ID = 'cart_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const EMAIL = 'email';
    const COUNTRY_ID = 'country_id';
    const REGION_ID = 'region_id';
    const REGION = 'region';
    const STREET = 'street';
    const COMPANY = 'company';
    const TELEPHONE = 'telephone';
    const FAX = 'fax';
    const POSTCODE = 'postcode';
    const CITY = 'city';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const MIDDLENAME = 'middlename';
    const PREFIX = 'prefix';
    const SUFFIX = 'suffix';
    const VAT_ID = 'vat_id';
    const IS_SAME_AS_BILLING = 'is_same_as_billing';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_ADDRESS_ID = 'customer_address_id';
    const ADDRESS_TYPE = 'address_type';
    const IS_SAVE_IN_ADDRESS_BOOK = 'is_save_in_address_book';
    const QTY = 'qty';
    const WEIGHT = 'weight';
    const SHIPPING_METHOD_CODE = 'shipping_method_code';
    const SHIPPING_CARRIER_CODE = 'shipping_carrier_code';
    /**#@-*/

    /**
     * Get address ID
     *
     * @return int|null
     */
    public function getAddressId();

    /**
     * Set address ID
     *
     * @param int $addressId
     * @return $this
     */
    public function setAddressId($addressId);

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
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get country ID
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Set country ID
     *
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId);

    /**
     * Get region ID
     *
     * @return int
     */
    public function getRegionId();

    /**
     * Set region ID
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * Get region name
     *
     * @return string
     */
    public function getRegion();

    /**
     * Set region name
     *
     * @param string $region
     * @return $this
     */
    public function setRegion($region);

    /**
     * Get street
     *
     * @return string[]
     */
    public function getStreet();

    /**
     * Set string
     *
     * @param string[] $street
     * @return $this
     */
    public function setStreet($street);

    /**
     * Get company
     *
     * @return string|null
     */
    public function getCompany();

    /**
     * Set company
     *
     * @param string $company
     * @return $this
     */
    public function setCompany($company);

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone();

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone);

    /**
     * Get fax
     *
     * @return string|null
     */
    public function getFax();

    /**
     * Set fax
     *
     * @param string $fax
     * @return $this
     */
    public function setFax($fax);

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode();

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode);

    /**
     * Get city
     *
     * @return string
     */
    public function getCity();

    /**
     * Set city
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname);

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname);

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename();

    /**
     * Set middle name
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename($middlename);

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix();

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix();

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this|null
     */
    public function setSuffix($suffix);

    /**
     * Get Vat id
     *
     * @return string|null
     */
    public function getVatId();

    /**
     * Set Vat id
     *
     * @param string $vatId
     * @return $this
     */
    public function setVatId($vatId);

    /**
     * Get is same as billing flag
     *
     * @return bool|null
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsSameAsBilling();

    /**
     * Set is same as billing flag
     *
     * @param bool $isSameAsBilling
     * @return $this
     */
    public function setIsSameAsBilling($isSameAsBilling);

    /**
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get customer address ID
     *
     * @return int|null
     */
    public function getCustomerAddressId();

    /**
     * Set customer address ID
     *
     * @param int $customerAddressId
     * @return $this
     */
    public function setCustomerAddressId($customerAddressId);

    /**
     * Get address type
     *
     * @return string
     */
    public function getAddressType();

    /**
     * Set address type
     *
     * @param string $addressType
     * @return $this
     */
    public function setAddressType($addressType);

    /**
     * Get is save in address book flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsSaveInAddressBook();

    /**
     * Set is save in address book flag
     *
     * @param bool $isSaveInAddressBook
     * @return $this
     */
    public function setIsSaveInAddressBook($isSaveInAddressBook);

    /**
     * Get address qty
     *
     * @return float
     */
    public function getQty();

    /**
     * Set address qty
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Get weight
     *
     * @return float
     */
    public function getWeight();

    /**
     * Set weight
     *
     * @param float $weight
     * @return $this
     */
    public function setWeight($weight);

    /**
     * Get shipping method code
     *
     * @return string
     */
    public function getShippingMethodCode();

    /**
     * Set shipping method code
     *
     * @param string $shippingMethodCode
     * @return $this
     */
    public function setShippingMethodCode($shippingMethodCode);

    /**
     * Get shipping carrier code
     *
     * @return string
     */
    public function getShippingCarrierCode();

    /**
     * Get shipping carrier code
     *
     * @param string $shippingCarrierCode
     * @return $this
     */
    public function setShippingCarrierCode($shippingCarrierCode);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressExtensionInterface $extensionAttributes
    );
}
