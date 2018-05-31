<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter;

/**
 * Class CutString
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter
 */
class CutString implements \Zend_Filter_Interface
{
    /**
     * @var int
     */
    private $length;

    /**
     * @param int $length
     */
    public function __construct($length = 0)
    {
        $this->length = $length;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        return substr($value, 0, $this->length);
    }
}
