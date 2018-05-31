<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface ProfileAddressInterface
 * @package Aheadworks\Sarp\Api\Data
 */
interface ProfileAddressInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ADDRESS_ID = 'address_id';
    const PROFILE_ID = 'profile_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const ADDRESS_TYPE = 'address_type';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_ADDRESS_ID = 'customer_address_id';
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
     * Get profile ID
     *
     * @return int
     */
    public function getProfileId();

    /**
     * Set profile ID
     *
     * @param int $profileId
     * @return $this
     */
    public function setProfileId($profileId);

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
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp\Api\Data\ProfileAddressExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp\Api\Data\ProfileAddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp\Api\Data\ProfileAddressExtensionInterface $extensionAttributes
    );
}
