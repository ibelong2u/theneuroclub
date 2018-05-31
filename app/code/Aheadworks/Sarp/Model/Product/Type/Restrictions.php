<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Product\Type;

/**
 * Class Restrictions
 * @package Aheadworks\Sarp\Model\Product\Type
 */
class Restrictions
{
    /**
     * @var array
     */
    private $supportedProductTypes = [];

    /**
     * @param array $supportedProductTypes
     */
    public function __construct($supportedProductTypes = [])
    {
        $this->supportedProductTypes = $supportedProductTypes;
    }

    /**
     * Get supported product types
     *
     * @return array
     */
    public function getSupportedProductTypes()
    {
        return $this->supportedProductTypes;
    }
}
