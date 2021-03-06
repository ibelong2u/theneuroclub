<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 17/05/2016
 * Time: 15:13
 */

namespace Magenest\Stripe\Model\ResourceModel\SubscriptionItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('Magenest\Stripe\Model\SubscriptionItem', 'Magenest\Stripe\Model\ResourceModel\SubscriptionItem');
    }
}
