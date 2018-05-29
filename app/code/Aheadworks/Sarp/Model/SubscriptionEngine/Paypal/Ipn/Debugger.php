<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Ipn;

use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ConfigProxy;
use Psr\Log\LoggerInterface;

/**
 * Class Debugger
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Ipn
 */
class Debugger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigProxy
     */
    private $paypalConfigProxy;

    /**
     * @var array
     */
    private $debugData = [];

    /**
     * @param LoggerInterface $logger
     * @param ConfigProxy $paypalConfigProxy
     */
    public function __construct(
        LoggerInterface $logger,
        ConfigProxy $paypalConfigProxy
    ) {
        $this->logger = $logger;
        $this->paypalConfigProxy = $paypalConfigProxy;
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
        if ($this->paypalConfigProxy->getValue('debug')) {
            $this->logger->debug(var_export($this->debugData, true));
            $this->debugData = [];
        }
    }
}
