<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Address\Converter;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\DataObject\Copy;

/**
 * Class CustomerAddressConverter
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Address
 */
class CustomerAddressConverter
{
    /**
     * @var SubscriptionsCartAddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var CustomerAddressFactory
     */
    private $customerAddressFactory;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param SubscriptionsCartAddressInterfaceFactory $addressFactory
     * @param CustomerAddressFactory $customerAddressFactory
     * @param RegionInterfaceFactory $regionFactory
     * @param Copy $objectCopyService
     */
    public function __construct(
        SubscriptionsCartAddressInterfaceFactory $addressFactory,
        CustomerAddressFactory $customerAddressFactory,
        RegionInterfaceFactory $regionFactory,
        Copy $objectCopyService
    ) {
        $this->addressFactory = $addressFactory;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->regionFactory = $regionFactory;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Convert from customer address
     *
     * @param CustomerAddress $customerAddress
     * @return SubscriptionsCartAddressInterface
     */
    public function fromCustomerAddress(CustomerAddress $customerAddress)
    {
        /** @var SubscriptionsCartAddressInterface $address */
        $address = $this->addressFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_customer_address',
            'to_cart_address',
            $customerAddress,
            $address
        );
        $region = $customerAddress->getRegion();
        if ($region) {
            $address
                ->setRegionId($region->getRegionId())
                ->setRegion($region->getRegion());
        }

        return $address;
    }

    /**
     * Convert into customer address
     *
     * @param SubscriptionsCartAddressInterface $address
     * @return CustomerAddress
     */
    public function toCustomerAddress(SubscriptionsCartAddressInterface $address)
    {
        /** @var CustomerAddress $customerAddress */
        $customerAddress = $this->customerAddressFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_cart_address',
            'to_customer_address',
            $address,
            $customerAddress
        );
        /** @var RegionInterface $region */
        $region = $this->regionFactory->create();
        $region->setRegionId($address->getRegionId());
        $customerAddress->setRegion($region);
        return $customerAddress;
    }
}
