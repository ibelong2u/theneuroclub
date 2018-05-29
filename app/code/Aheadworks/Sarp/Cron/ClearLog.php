<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Cron;

use Aheadworks\Sarp\Model\Config;
use Aheadworks\Sarp\Model\ResourceModel\Logger\LogEntry\CollectionFactory;

/**
 * Class ClearLog
 * @package Aheadworks\Sarp\Cron
 */
class ClearLog
{
    /**
     * Const to convert days to seconds
     */
    const LIFETIME = 86400;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Config $config,
        CollectionFactory $collectionFactory
    ) {
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Perform removing of old log records
     *
     * @return void
     */
    public function execute()
    {
        $logRecordsLifetimeInDays = $this->config->getKeepLogForDaysValue();
        if (!empty($logRecordsLifetimeInDays)) {
            $logRecordsLifetimeInSeconds = $logRecordsLifetimeInDays * self::LIFETIME;
            $logRecords = $this->collectionFactory->create();
            $logRecords->addFieldToFilter(
                'date_time',
                [
                    'to' => date("Y-m-d H:i:s", time() - $logRecordsLifetimeInSeconds)
                ]
            );
            $logRecords->walk('delete');
        }
    }
}
