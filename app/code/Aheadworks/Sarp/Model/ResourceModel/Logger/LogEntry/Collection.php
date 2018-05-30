<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\ResourceModel\Logger\LogEntry;

use Aheadworks\Sarp\Model\Logger\LogEntry;
use Aheadworks\Sarp\Model\ResourceModel\Logger\LogEntry as LogEntryResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'log_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(LogEntry::class, LogEntryResource::class);
    }
}
