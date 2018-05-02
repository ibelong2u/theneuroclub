<?php
/**
 * Copyright © 2015 Quinoid. All rights reserved.
 */
namespace Quinoid\PriceDiscount\Model\ResourceModel;

/**
 * Bundlediscount resource
 */
class Bundlediscount extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('pricediscount_bundlediscount', 'id');
    }

  
}
