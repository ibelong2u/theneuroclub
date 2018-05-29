<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Item\QuantityValidator;

/**
 * Class ItemQtyList
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Item\QuantityValidator
 */
class ItemQtyList
{
    /**
     * @var array
     */
    private $checkedCartItems = [];

    /**
     * Get product quantity includes information from all quote items
     *
     * @param int $productId
     * @param int $cartItemId
     * @param int $cartId
     * @param float $itemQty
     * @return float
     */
    public function getQty($productId, $cartItemId, $cartId, $itemQty)
    {
        $qty = $itemQty;
        if (isset($this->checkedCartItems[$cartId][$productId]['qty'])
            && !in_array($cartItemId, $this->checkedCartItems[$cartId][$productId]['items'])
        ) {
            $qty += $this->checkedCartItems[$cartId][$productId]['qty'];
        }

        $this->checkedCartItems[$cartId][$productId]['qty'] = $qty;
        $this->checkedCartItems[$cartId][$productId]['items'][] = $cartItemId;

        return $qty;
    }
}
