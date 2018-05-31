<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Customer\Subscription\Info\Address;

use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Options as CustomerOptions;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Form
 *
 * @method ProfileAddressInterface getAddress()
 *
 * @package Aheadworks\Sarp\Block\Customer\Subscription\Info\Address
 */
class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    /**
     * @var CustomerOptions
     */
    private $customerOptions;

    /**
     * @var CountryCollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * @var array
     */
    private $countryOptions;

    /**
     * @var DirectoryHelper
     */
    private $directoryHelper;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'customer/subscription/info/address/form.phtml';

    /**
     * @param Context $context
     * @param AddressMetadataInterface $addressMetadata
     * @param CustomerOptions $customerOptions
     * @param CountryCollectionFactory $countryCollectionFactory
     * @param DirectoryHelper $directoryHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        AddressMetadataInterface $addressMetadata,
        CustomerOptions $customerOptions,
        CountryCollectionFactory $countryCollectionFactory,
        DirectoryHelper $directoryHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->addressMetadata = $addressMetadata;
        $this->customerOptions = $customerOptions;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * Get field html Id
     *
     * @param string $fieldName
     * @return string
     */
    public function getFieldHtmlId($fieldName)
    {
        return sprintf('%s-address-%s', $this->getAddress()->getAddressType(), $fieldName);
    }

    /**
     * Get attribute store label
     *
     * @param string $attributeCode
     * @return string
     */
    public function getAttributeStoreLabel($attributeCode)
    {
        $attribute = $this->getAttributeMetadata($attributeCode);
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * Get attribute frontend class name
     *
     * @param string $attributeCode
     * @return string
     */
    public function getAttributeFrontendClass($attributeCode)
    {
        $attributeMetadata = $this->getAttributeMetadata($attributeCode);
        return $attributeMetadata ? $attributeMetadata->getFrontendClass() : '';
    }

    /**
     * Check if prefix attribute is visible
     *
     * @return bool
     */
    public function isPrefixVisible()
    {
        return $this->isAttributeVisible('prefix');
    }

    /**
     * Check if middle name attribute is visible
     *
     * @return bool
     */
    public function isMiddlenameVisible()
    {
        return $this->isAttributeVisible('middlename');
    }

    /**
     * Check if suffix attribute is visible
     *
     * @return bool
     */
    public function isSuffixVisible()
    {
        return $this->isAttributeVisible('suffix');
    }

    /**
     * Check if prefix attribute is required
     *
     * @return bool
     */
    public function isPrefixRequired()
    {
        return $this->isAttributeRequired('prefix');
    }

    /**
     * Check if middle name attribute is required
     *
     * @return bool
     */
    public function isMiddlenameRequired()
    {
        return $this->isAttributeRequired('middlename');
    }

    /**
     * Check if suffix attribute is required
     *
     * @return bool
     */
    public function isSuffixRequired()
    {
        return $this->isAttributeRequired('suffix');
    }

    /**
     * Get prefix options
     *
     * @return array|bool
     */
    public function getPrefixOptions()
    {
        $prefixOptions = $this->customerOptions->getNamePrefixOptions();
        if ($prefixOptions) {
            $oldPrefix = $this->escapeHtml(trim($this->getAddress()->getPrefix()));
            $prefixOptions[$oldPrefix] = $oldPrefix;
        }
        return $prefixOptions;
    }

    /**
     * Get suffix options
     *
     * @return array|bool
     */
    public function getSuffixOptions()
    {
        $suffixOptions = $this->customerOptions->getNameSuffixOptions();
        if ($suffixOptions) {
            $oldSuffix = $this->escapeHtml(trim($this->getAddress()->getSuffix()));
            $suffixOptions[$oldSuffix] = $oldSuffix;
        }
        return $suffixOptions;
    }

    /**
     * Get country options
     *
     * @return array
     */
    public function getCountryOptions()
    {
        if (!$this->countryOptions) {
            $destinations = (string)$this->_scopeConfig->getValue(
                'general/country/destinations',
                ScopeInterface::SCOPE_STORE
            );
            $destinations = $destinations ? explode(',', $destinations) : [];

            /** @var CountryCollection $collection */
            $collection = $this->countryCollectionFactory->create();
            $this->countryOptions = $collection
                ->setForegroundCountries($destinations)
                ->toOptionArray();
        }
        return $this->countryOptions;
    }

    /**
     * Check if attribute is visible
     *
     * @param string $attributeCode
     * @return bool
     */
    private function isAttributeVisible($attributeCode)
    {
        $attributeMetadata = $this->getAttributeMetadata($attributeCode);
        return $attributeMetadata ? $attributeMetadata->isVisible() : false;
    }

    /**
     * Check if attribute is required
     *
     * @param string $attributeCode
     * @return bool
     */
    private function isAttributeRequired($attributeCode)
    {
        $attributeMetadata = $this->getAttributeMetadata($attributeCode);
        return $attributeMetadata ? $attributeMetadata->isRequired() : false;
    }

    /**
     * Retrieve attribute metadata
     *
     * @param string $attributeCode
     * @return AttributeMetadataInterface|null
     */
    private function getAttributeMetadata($attributeCode)
    {
        try {
            return $this->addressMetadata->getAttributeMetadata($attributeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Check if optional region allowed
     *
     * @return bool
     */
    public function isOptionalRegionAllowed()
    {
        return $this->_scopeConfig->isSetFlag('general/region/display_all', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get region json string
     *
     * @return string
     */
    public function getRegionJson()
    {
        return $this->directoryHelper->getRegionJson();
    }

    /**
     * Get countries with optional zip
     *
     * @return array|string
     */
    public function getCountriesWithOptionalZip()
    {
        return $this->directoryHelper->getCountriesWithOptionalZip(true);
    }

    /**
     * Get save address url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp/profile/saveAddress',
            ['address_id' => $this->getAddress()->getAddressId()]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getAddress()) {
            return '';
        }
        return parent::_toHtml();
    }
}
