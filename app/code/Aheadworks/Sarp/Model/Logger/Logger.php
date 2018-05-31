<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger;

use Aheadworks\Sarp\Model\Logger\Data\ResolverPool;
use Aheadworks\Sarp\Model\Logger\Source\Level;
use Aheadworks\Sarp\Model\ResourceModel\Logger\LogEntry as LogEntryResource;
use Magento\Framework\DataObject;

/**
 * Class Logger
 * @package Aheadworks\Sarp\Model\Logger
 */
class Logger implements LoggerInterface
{
    /**
     * @var LogEntryFactory
     */
    private $logEntryFactory;

    /**
     * @var LogEntryResource
     */
    private $logEntryResource;

    /**
     * @var ResolverPool
     */
    private $logDataResolverPool;

    /**
     * @param LogEntryFactory $logEntryFactory
     * @param LogEntryResource $logEntryResource
     * @param ResolverPool $logDataResolverPool
     */
    public function __construct(
        LogEntryFactory $logEntryFactory,
        LogEntryResource $logEntryResource,
        ResolverPool $logDataResolverPool
    ) {
        $this->logEntryFactory = $logEntryFactory;
        $this->logEntryResource = $logEntryResource;
        $this->logDataResolverPool = $logDataResolverPool;
    }

    /**
     * {@inheritdoc}
     */
    public function notice($object, $entryType, array $additionalInfo = [])
    {
        $this->log(Level::NOTICE, $object, $entryType, $additionalInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($object, $entryType, array $additionalInfo = [])
    {
        $this->log(Level::WARNING, $object, $entryType, $additionalInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function error($object, $entryType, array $additionalInfo = [])
    {
        $this->log(Level::ERROR, $object, $entryType, $additionalInfo);
    }

    /**
     * Log data
     *
     * @param string $level
     * @param DataObject $object
     * @param string $entryType
     * @param array $additionalInfo
     * @throws \Exception
     */
    private function log($level, $object, $entryType, array $additionalInfo = [])
    {
        $resolver = $this->logDataResolverPool->getResolver($entryType);
        $entryData = $resolver->getEntryData($object, $additionalInfo);

        /** @var LogEntry $logEntry */
        $logEntry = $this->logEntryFactory->create(['data' => $entryData]);
        $logEntry->setLevel($level);
        $this->logEntryResource->save($logEntry);
    }
}
