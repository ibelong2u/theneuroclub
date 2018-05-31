<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook;

use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Config;
use Psr\Log\LoggerInterface;

/**
 * Class Debugger
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook
 */
class Debugger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $debugData = [];

    /**
     * @param LoggerInterface $logger
     * @param Config $config
     */
    public function __construct(
        LoggerInterface $logger,
        Config $config
    ) {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Add debug data
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addDebugData($key, $value)
    {
        $this->debugData[$key] = $value;
        return $this;
    }

    /**
     * Lof debug data into file
     *
     * @return void
     */
    public function debug()
    {
        if ($this->config->isDebugOn()) {
            $this->logger->debug(var_export($this->debugData, true));
            $this->debugData = [];
        }
    }
}
