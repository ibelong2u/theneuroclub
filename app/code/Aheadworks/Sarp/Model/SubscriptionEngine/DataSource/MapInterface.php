<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\DataSource;

/**
 * Interface MapInterface
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\DataSource
 */
interface MapInterface
{
    /**
     * Return array of option map
     *
     * @return array Format: array(array('fromValue' =>  array('value' => '<value>', 'label' => '<label>'), ...), ...)
     */
    public function toOptionMapArray();
}
