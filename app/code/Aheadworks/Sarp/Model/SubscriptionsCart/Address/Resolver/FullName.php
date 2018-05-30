<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Address\Resolver;

use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class FullName
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Address\Resolver
 */
class FullName
{
    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectFactory $dataObjectFactory
     * @param EavConfig $eavConfig
     */
    public function __construct(
        DataObjectProcessor $dataObjectProcessor,
        DataObjectFactory $dataObjectFactory,
        EavConfig $eavConfig
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Get full name
     *
     * @param array|SubscriptionsCartAddressInterface|ProfileAddressInterface $address
     * @return string
     */
    public function getFullName($address)
    {
        $fullName = '';
        $address = $this->getAddress($address);

        $prefixAttribute = $this->eavConfig->getAttribute('customer_address', 'prefix');
        if ($prefixAttribute->getIsVisible() && $address->getPrefix()) {
            $fullName .= $address->getPrefix() . ' ';
        }

        $fullName .= $address->getFirstname();

        $middleNameAttribute = $this->eavConfig->getAttribute('customer_address', 'middlename');
        if ($middleNameAttribute->getIsVisible() && $address->getMiddlename()) {
            $fullName .= ' ' . $address->getMiddlename();
        }

        $fullName .= ' ' . $address->getLastname();

        $suffixAttribute = $this->eavConfig->getAttribute('customer_address', 'suffix');
        if ($suffixAttribute->getIsVisible() && $address->getSuffix()) {
            $fullName .= ' ' . $address->getSuffix();
        }

        return $fullName;
    }

    /**
     * Get address object
     *
     * @param array|SubscriptionsCartAddressInterface|ProfileAddressInterface $address
     * @return DataObject
     */
    private function getAddress($address)
    {
        if ($address instanceof SubscriptionsCartAddressInterface) {
            $addressData = $this->dataObjectProcessor->buildOutputDataArray(
                $address,
                SubscriptionsCartAddressInterface::class
            );
        } elseif ($address instanceof ProfileAddressInterface) {
            $addressData = $this->dataObjectProcessor->buildOutputDataArray(
                $address,
                ProfileAddressInterface::class
            );
        } else {
            $addressData = $address;
        }
        return $this->dataObjectFactory->create($addressData);
    }
}
