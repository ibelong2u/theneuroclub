<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDateTime;

/**
 * Class DataResolver
 * @package Aheadworks\Sarp\Model\SubscriptionEngine
 */
class DataResolver
{
    /**
     * @var CoreDateTime
     */
    private $coreDate;

    /**
     * @param CoreDateTime $coreDate
     */
    public function __construct(CoreDateTime $coreDate)
    {
        $this->coreDate = $coreDate;
    }

    /**
     * Get profile description
     *
     * @param ProfileInterface $profile
     * @return \Magento\Framework\Phrase
     */
    public function getProfileDescription(ProfileInterface $profile)
    {
        $productNames = [];
        foreach ($profile->getItems() as $item) {
            $productNames[] = $item->getName();
        }
        return $this->generateProfileDescription($productNames);
    }

    /**
     * Get profile description using subscription cart instance
     *
     * @param SubscriptionsCartInterface $cart
     * @return \Magento\Framework\Phrase
     */
    public function getProfileDescriptionUsingCart(SubscriptionsCartInterface $cart)
    {
        $productNames = [];
        foreach ($cart->getItems() as $item) {
            $productNames[] = $item->getName();
        }
        return $this->generateProfileDescription($productNames);
    }

    /**
     * Generate profile description
     *
     * @param array $productNames
     * @return \Magento\Framework\Phrase
     */
    private function generateProfileDescription(array $productNames)
    {
        return count($productNames) > 1
            ? __('Recurring profile for products: %1', implode(', ', $productNames))
            : __('Recurring profile for product: %1', $productNames[0]);
    }

    /**
     * Get credit card expiration date
     *
     * @param string $ccExpMonth
     * @param string $ccExpYear
     * @return string
     */
    public function getCcExpirationDate($ccExpMonth, $ccExpYear)
    {
        return $this->coreDate->date('Y-m', $ccExpYear . '-' . $ccExpMonth);
    }
}
