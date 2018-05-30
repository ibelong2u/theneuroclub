<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Profile\Address;

use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Api\Data\ProfileAddressInterfaceFactory;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Magento\Framework\DataObject\Copy;

/**
 * Class Converter
 * @package Aheadworks\Sarp\Model\Profile\Address
 */
class Converter
{
    /**
     * @var ProfileAddressInterfaceFactory
     */
    private $profileAddressFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param ProfileAddressInterfaceFactory $profileAddressFactory
     * @param Copy $objectCopyService
     */
    public function __construct(
        ProfileAddressInterfaceFactory $profileAddressFactory,
        Copy $objectCopyService
    ) {
        $this->profileAddressFactory = $profileAddressFactory;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Convert from cart address
     *
     * @param SubscriptionsCartAddressInterface $address
     * @return ProfileAddressInterface
     */
    public function fromCartAddress(SubscriptionsCartAddressInterface $address)
    {
        /** @var ProfileAddressInterface $profileAddress */
        $profileAddress = $this->profileAddressFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_profile_address',
            'from_cart_address',
            $address,
            $profileAddress
        );
        return $profileAddress;
    }
}
