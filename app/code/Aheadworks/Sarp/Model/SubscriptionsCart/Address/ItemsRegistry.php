<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Address;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;

/**
 * Class ItemsRegistry
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Address
 */
class ItemsRegistry
{
    /**
     * @var SubscriptionsCartItemInterface[]
     */
    private $items = [];

    /**
     * @var SubscriptionsCartItemInterface[]
     */
    private $innerItems = [];

    /**
     * Retrieve address items
     *
     * @param SubscriptionsCartAddressInterface $address
     * @param SubscriptionsCartInterface $cart
     * @return SubscriptionsCartItemInterface[]
     */
    public function retrieve($address, $cart)
    {
        $addressId = $address->getAddressId();
        if (!$addressId || !isset($this->items[$addressId])) {
            $addressType = $address->getAddressType();
            $canRetrieve = $cart->getIsVirtual()
                ? $addressType == Address::TYPE_BILLING
                : $addressType == Address::TYPE_SHIPPING;
            if ($canRetrieve) {
                if (!$addressId) {
                    return $cart->getItems();
                }
                $this->items[$addressId] = $cart->getItems();
            } else {
                $this->items[$addressId] = [];
            }
        }
        return $this->items[$addressId];
    }

    /**
     * Retrieve inner address items (including non visible)
     *
     * @param SubscriptionsCartAddressInterface $address
     * @param SubscriptionsCartInterface $cart
     * @return SubscriptionsCartItemInterface[]
     */
    public function retrieveInner($address, $cart)
    {
        $addressId = $address->getAddressId();
        if (!$addressId || !isset($this->innerItems[$addressId])) {
            $addressType = $address->getAddressType();
            $canRetrieve = $cart->getIsVirtual()
                ? $addressType == Address::TYPE_BILLING
                : $addressType == Address::TYPE_SHIPPING;
            if ($canRetrieve) {
                if (!$addressId) {
                    return $cart->getInnerItems();
                }
                $this->innerItems[$addressId] = $cart->getInnerItems();
            } else {
                $this->innerItems[$addressId] = [];
            }
        }
        return $this->innerItems[$addressId];
    }

    /**
     * Retrieve child address items
     *
     * @param SubscriptionsCartAddressInterface $address
     * @param SubscriptionsCartInterface $cart
     * @param int $itemId
     * @return SubscriptionsCartItemInterface[]
     */
    public function retrieveChild($address, $cart, $itemId)
    {
        $child = [];
        foreach ($this->retrieveInner($address, $cart) as $innerItem) {
            if ($innerItem->getParentItemId() == $itemId) {
                $child[] = $innerItem;
            }
        }
        return $child;
    }
}
