<?php
/*
Author Kavitha
*/

namespace Quinoid\Subscription\Observer\Controller;
use Psr\Log\LoggerInterface;

class ActionPredispatchCheckoutCartAdd implements \Magento\Framework\Event\ObserverInterface
{

  /** @var LoggerInterface  */
    protected $logger;

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
          $this->logger->log("observer");
    }
}
