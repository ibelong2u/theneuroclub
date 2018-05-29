<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Order\Address;

use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterfaceFactory;
use Magento\Framework\DataObject\Copy;

/**
 * Class Converter
 * @package Aheadworks\Sarp\Model\Order\Address
 */
class Converter
{
    /**
     * @var OrderAddressInterfaceFactory
     */
    private $orderAddressFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param OrderAddressInterfaceFactory $orderAddressFactory
     * @param Copy $objectCopyService
     */
    public function __construct(
        OrderAddressInterfaceFactory $orderAddressFactory,
        Copy $objectCopyService
    ) {
        $this->orderAddressFactory = $orderAddressFactory;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Convert from subscription profile address
     *
     * @param ProfileAddressInterface $address
     * @return OrderAddressInterface
     */
    public function fromProfileAddress(ProfileAddressInterface $address)
    {
        /** @var OrderAddressInterface $orderAddress */
        $orderAddress = $this->orderAddressFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_profile_address',
            'to_order_address',
            $address,
            $orderAddress
        );
        return $orderAddress;
    }
}
