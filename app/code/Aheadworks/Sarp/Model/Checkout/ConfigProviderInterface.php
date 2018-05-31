<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Checkout;

/**
 * Interface ConfigProviderInterface
 * @package Aheadworks\Sarp\Model\Checkout
 */
interface ConfigProviderInterface
{
    /**
     * Get config
     *
     * @return array
     */
    public function getConfig();
}
