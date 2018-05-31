<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter;

/**
 * Class FilterInt
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter
 */
class FilterInt implements \Zend_Filter_Interface
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        return (int)$value;
    }
}
