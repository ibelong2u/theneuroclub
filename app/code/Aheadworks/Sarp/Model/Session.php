<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model;

/**
 * Class Session
 *
 * @method $this setLastSuccessCartId(int $cartId)
 * @method $this setLastProfileId(int $profileId)
 * @method int getLastSuccessCartId()
 * @method int getLastProfileId()
 *
 * @package Aheadworks\Sarp\Model
 */
class Session extends \Magento\Framework\Session\SessionManager
{
    /**
     * Get subscription cart ID
     *
     * @param int $websiteId
     * @return int
     */
    public function getCartId($websiteId)
    {
        return $this->getData($this->getCartIdKey($websiteId));
    }

    /**
     * Set subscription cart ID
     *
     * @param int $cartId
     * @param int $websiteId
     * @return void
     */
    public function setCartId($cartId, $websiteId)
    {
        $this->storage->setData($this->getCartIdKey($websiteId), $cartId);
    }

    /**
     * Get cart ID key
     *
     * @param int $websiteId
     * @return string
     */
    private function getCartIdKey($websiteId)
    {
        return 'cart_id_' . $websiteId;
    }
}
