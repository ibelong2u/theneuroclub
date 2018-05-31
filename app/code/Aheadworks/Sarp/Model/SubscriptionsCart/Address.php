<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressExtensionInterface;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart\Address as AddressResource;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Resolver\Region as RegionResolver;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Validator as AddressValidator;
use Magento\Customer\Api\AddressRepositoryInterface as CustomerAddressRepository;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Address
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Address extends AbstractModel implements SubscriptionsCartAddressInterface
{
    /**
     * Address types constants
     */
    const TYPE_SHIPPING = 'shipping';
    const TYPE_BILLING = 'billing';

    /**
     * @var AddressValidator
     */
    private $validator;

    /**
     * @var CustomerAddressRepository
     */
    private $customerAddressRepository;

    /**
     * @var RegionResolver
     */
    private $regionResolver;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param AddressValidator $validator
     * @param CustomerAddressRepository $customerAddressRepository
     * @param RegionResolver $regionResolver
     * @param Copy $objectCopyService
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AddressValidator $validator,
        CustomerAddressRepository $customerAddressRepository,
        RegionResolver $regionResolver,
        Copy $objectCopyService,
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
        $this->validator = $validator;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->regionResolver = $regionResolver;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(AddressResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressId()
    {
        return $this->getData(self::ADDRESS_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddressId($addressId)
    {
        return $this->setData(self::ADDRESS_ID, $addressId);
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
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryId()
    {
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegionId()
    {
        return $this->getData(self::REGION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegion()
    {
        return $this->getData(self::REGION);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * {@inheritdoc}
     */
    public function getStreet()
    {
        return $this->getData(self::STREET) ? : [];
    }

    /**
     * {@inheritdoc}
     */
    public function setStreet($street)
    {
        return $this->setData(self::STREET, $street);
    }

    /**
     * {@inheritdoc}
     */
    public function getCompany()
    {
        return $this->getData(self::COMPANY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCompany($company)
    {
        return $this->setData(self::COMPANY, $company);
    }

    /**
     * {@inheritdoc}
     */
    public function getTelephone()
    {
        return $this->getData(self::TELEPHONE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * {@inheritdoc}
     */
    public function getFax()
    {
        return $this->getData(self::FAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setFax($fax)
    {
        return $this->setData(self::FAX, $fax);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostcode()
    {
        return $this->getData(self::POSTCODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstname()
    {
        return $this->getData(self::FIRSTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstname($firstname)
    {
        return $this->setData(self::FIRSTNAME, $firstname);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastname()
    {
        return $this->getData(self::LASTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastname($lastname)
    {
        return $this->setData(self::LASTNAME, $lastname);
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddlename()
    {
        return $this->getData(self::MIDDLENAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setMiddlename($middlename)
    {
        return $this->setData(self::MIDDLENAME, $middlename);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix()
    {
        return $this->getData(self::PREFIX);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix($prefix)
    {
        return $this->setData(self::PREFIX, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function getSuffix()
    {
        return $this->getData(self::SUFFIX);
    }

    /**
     * {@inheritdoc}
     */
    public function setSuffix($suffix)
    {
        return $this->setData(self::SUFFIX, $suffix);
    }

    /**
     * {@inheritdoc}
     */
    public function getVatId()
    {
        return $this->getData(self::VAT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setVatId($vatId)
    {
        return $this->setData(self::VAT_ID, $vatId);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsSameAsBilling()
    {
        return $this->getData(self::IS_SAME_AS_BILLING);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsSameAsBilling($isSameAsBilling)
    {
        return $this->setData(self::IS_SAME_AS_BILLING, $isSameAsBilling);
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
    public function getCustomerAddressId()
    {
        return $this->getData(self::CUSTOMER_ADDRESS_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerAddressId($customerAddressId)
    {
        return $this->setData(self::CUSTOMER_ADDRESS_ID, $customerAddressId);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressType()
    {
        return $this->getData(self::ADDRESS_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddressType($addressType)
    {
        return $this->setData(self::ADDRESS_TYPE, $addressType);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsSaveInAddressBook()
    {
        return $this->getData(self::IS_SAVE_IN_ADDRESS_BOOK);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsSaveInAddressBook($isSaveInAddressBook)
    {
        return $this->setData(self::IS_SAVE_IN_ADDRESS_BOOK, $isSaveInAddressBook);
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
    public function getWeight()
    {
        return $this->getData(self::WEIGHT);
    }

    /**
     * {@inheritdoc}
     */
    public function setWeight($weight)
    {
        return $this->setData(self::WEIGHT, $weight);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingMethodCode()
    {
        return $this->getData(self::SHIPPING_METHOD_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingMethodCode($shippingMethodCode)
    {
        return $this->setData(self::SHIPPING_METHOD_CODE, $shippingMethodCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingCarrierCode()
    {
        return $this->getData(self::SHIPPING_CARRIER_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingCarrierCode($shippingCarrierCode)
    {
        return $this->setData(self::SHIPPING_CARRIER_CODE, $shippingCarrierCode);
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
    public function setExtensionAttributes(SubscriptionsCartAddressExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        $customerAddressId = $this->getCustomerAddressId();
        if ($customerAddressId) {
            try {
                $customerAddress = $this->customerAddressRepository->getById($customerAddressId);
            } catch (NoSuchEntityException $e) {
            }
            $this->objectCopyService->copyFieldsetToTarget(
                'aw_sarp_customer_address',
                'to_cart_address',
                $customerAddress,
                $this
            );
            $region = $customerAddress->getRegion();
            if ($region) {
                $this
                    ->setRegionId($region->getRegionId());
            }
        }
        $this->setRegion(
            $this->regionResolver->getRegion(
                $this->getRegionId(),
                $this->getRegion(),
                $this->getCountryId()
            )
        );

        parent::beforeSave();
    }

    /**
     * {@inheritdoc}
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }
}
