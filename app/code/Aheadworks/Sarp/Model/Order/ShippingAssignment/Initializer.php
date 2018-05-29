<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Order\ShippingAssignment;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\Order\Item\Converter as ItemConverter;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\ShippingAssignmentInterface;
use Magento\Sales\Api\Data\ShippingInterface;
use Magento\Sales\Api\Data\ShippingInterfaceFactory;
use Magento\Framework\DataObject\Copy;

/**
 * Class Initializer
 * @package Aheadworks\Sarp\Model\Order\ShippingAssignment
 */
class Initializer
{
    /**
     * @var ShippingInterfaceFactory
     */
    private $shippingFactory;

    /**
     * @var ItemConverter
     */
    private $itemConverter;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param ShippingInterfaceFactory $shippingFactory
     * @param ItemConverter $itemConverter
     * @param Copy $objectCopyService
     */
    public function __construct(
        ShippingInterfaceFactory $shippingFactory,
        ItemConverter $itemConverter,
        Copy $objectCopyService
    ) {
        $this->shippingFactory = $shippingFactory;
        $this->itemConverter = $itemConverter;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Init shipping assignment
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param ProfileInterface $profile
     * @param OrderAddressInterface $shippingAddress
     * @return ShippingAssignmentInterface
     */
    public function init(
        ShippingAssignmentInterface $shippingAssignment,
        ProfileInterface $profile,
        OrderAddressInterface $shippingAddress
    ) {
        /** @var ShippingInterface $shipping */
        $shipping = $this->shippingFactory->create();
        $shipping
            ->setAddress($shippingAddress)
            ->setMethod($profile->getShippingMethod());
        $shippingAssignment->setShipping($shipping);
        return $shippingAssignment;
    }
}
