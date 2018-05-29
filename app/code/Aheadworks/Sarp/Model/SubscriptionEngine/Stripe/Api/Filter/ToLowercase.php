<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Filter;

/**
 * Class ToLowercase
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Filter
 */
class ToLowercase implements \Zend_Filter_Interface
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        return strtolower($value);
    }
}
