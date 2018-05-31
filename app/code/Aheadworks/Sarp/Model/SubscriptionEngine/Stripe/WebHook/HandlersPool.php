<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook;

use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook\Handlers\InvoiceCreated;
use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook\Handlers\InvoicePaid;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class HandlersPool
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook
 */
class HandlersPool
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $handlers = [
        Handler::EVENT_TYPE_INVOICE_CREATED => InvoiceCreated::class,
        Handler::EVENT_TYPE_INVOICE_PAYMENT_SUCCEED => InvoicePaid::class
    ];

    /**
     * @var HandlerInterface[]
     */
    private $handlerInstances = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $handlers
     */
    public function __construct(ObjectManagerInterface $objectManager, $handlers = [])
    {
        $this->objectManager = $objectManager;
        $this->handlers = array_merge($this->handlers, $handlers);
    }

    /**
     * Retrieve handler instance for given event type
     *
     * @param string $eventType
     * @return HandlerInterface|null
     * @throws \Exception
     */
    public function getHandler($eventType)
    {
        if (!isset($this->handlerInstances[$eventType])) {
            if (!isset($this->handlers[$eventType])) {
                return null;
            }
            $handlerInstance = $this->objectManager->create($this->handlers[$eventType]);
            if (!$handlerInstance instanceof HandlerInterface) {
                throw new \Exception(
                    sprintf('Handler instance %s does not implement required interface.', $eventType)
                );
            }
            $this->handlerInstances[$eventType] = $handlerInstance;
        }
        return $this->handlerInstances[$eventType];
    }
}
