<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart\Address;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressSearchResultsInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;

/**
 * Class SearchResults
 * @package Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart\Address
 */
class SearchResults extends \Magento\Framework\Api\SearchResults implements
    SubscriptionsCartAddressSearchResultsInterface
{
    const KEY_SHIPPING_ADDRESS = 'shipping_address';
    const KEY_BILLING_ADDRESS = 'billing_address';

    /**
     * {@inheritdoc}
     */
    public function getShippingAddress()
    {
        if ($this->getItems() && !$this->_get(self::KEY_SHIPPING_ADDRESS)) {
            $this->setData(self::KEY_SHIPPING_ADDRESS, $this->findByAddressType(Address::TYPE_SHIPPING));
        }
        return $this->_get(self::KEY_SHIPPING_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress()
    {
        if ($this->getItems() && !$this->_get(self::KEY_BILLING_ADDRESS)) {
            $this->setData(self::KEY_BILLING_ADDRESS, $this->findByAddressType(Address::TYPE_BILLING));
        }
        return $this->_get(self::KEY_BILLING_ADDRESS);
    }

    /**
     * Find address item by address type
     *
     * @param string $addressType
     * @return SubscriptionsCartAddressInterface|null
     */
    private function findByAddressType($addressType)
    {
        /** @var SubscriptionsCartAddressInterface $item */
        foreach ($this->getItems() as $item) {
            if ($item->getAddressType() == $addressType) {
                return $item;
            }
        }
        return null;
    }
}
