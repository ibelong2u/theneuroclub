<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\CustomerData;

use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor as CartPersistor;
use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Class SubscriptionCart
 * @package Aheadworks\Sarp\CustomerData
 */
class SubscriptionCart implements SectionSourceInterface
{
    /**
     * @var CartPersistor
     */
    private $cartPersistor;

    /**
     * @param CartPersistor $cartPersistor
     */
    public function __construct(CartPersistor $cartPersistor)
    {
        $this->cartPersistor = $cartPersistor;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $itemsQty = 0;
        foreach ($this->cartPersistor->getSubscriptionCart()->getItems() as $item) {
            $itemsQty += $item->getQty();
        }
        return [
            'itemsCount' => $itemsQty
        ];
    }
}
