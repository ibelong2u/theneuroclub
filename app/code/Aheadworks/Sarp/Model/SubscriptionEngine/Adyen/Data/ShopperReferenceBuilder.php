<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Data;

use Aheadworks\Sarp\Api\Data\ProfileInterface;

/**
 * Class ShopperReferenceBuilder
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Data
 */
class ShopperReferenceBuilder
{
    /**
     * Build shopper reference
     *
     * @param string $referenceId
     * @param int $storeId
     * @param int|null $customerId
     * @return string
     */
    public function build($referenceId, $storeId, $customerId = null)
    {
        return $customerId
            ? $customerId . '_' . $referenceId . '_' . $storeId
            : 'guest_' . $referenceId . '_' . $storeId;
    }

    /**
     * Build shopper reference from profile entity
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function buildUsingProfile(ProfileInterface $profile)
    {
        return $this->build(
            $profile->getReferenceId(),
            $profile->getStoreId(),
            $profile->getCustomerId()
        );
    }
}
