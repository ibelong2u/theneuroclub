<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Profile\Item;

use Aheadworks\Sarp\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp\Api\Data\ProfileItemInterfaceFactory;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Magento\Framework\DataObject\Copy;

/**
 * Class Converter
 * @package Aheadworks\Sarp\Model\Profile\Item
 */
class Converter
{
    /**
     * @var ProfileItemInterfaceFactory
     */
    private $profileItemFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param ProfileItemInterfaceFactory $profileItemFactory
     * @param Copy $objectCopyService
     */
    public function __construct(
        ProfileItemInterfaceFactory $profileItemFactory,
        Copy $objectCopyService
    ) {
        $this->profileItemFactory = $profileItemFactory;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Convert from cart item
     *
     * @param SubscriptionsCartItemInterface $item
     * @param SubscriptionsCartInterface $cart
     * @return ProfileItemInterface
     */
    public function fromCartItem(SubscriptionsCartItemInterface $item, SubscriptionsCartInterface $cart)
    {
        /** @var ProfileItemInterface $profileItem */
        $profileItem = $this->profileItemFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_profile_item',
            'from_cart_item',
            $item,
            $profileItem
        );

        $profileChildItems = [];
        foreach ($this->getChildItems($item->getItemId(), $cart) as $childItem) {
            /** @var ProfileItemInterface $profileChildItem */
            $profileChildItem = $this->profileItemFactory->create();
            $this->objectCopyService->copyFieldsetToTarget(
                'aw_sarp_convert_profile_item',
                'from_cart_item',
                $childItem,
                $profileChildItem
            );
            $profileChildItems[] = $profileChildItem;
        }
        $profileItem->setChildItems($profileChildItems);
        return $profileItem;
    }

    /**
     * Get child items
     *
     * @param int $itemId
     * @param SubscriptionsCartInterface $cart
     * @return SubscriptionsCartItemInterface[]
     */
    private function getChildItems($itemId, $cart)
    {
        $childItems = [];
        foreach ($cart->getInnerItems() as $item) {
            if ($item->getParentItemId() == $itemId) {
                $childItems[] = $item;
            }
        }
        return $childItems;
    }
}
