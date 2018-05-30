<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook;

use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

/**
 * Class Handler
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook
 */
class Handler
{
    /**
     * Event types
     */
    const EVENT_TYPE_INVOICE_CREATED = 'invoice.created';
    const EVENT_TYPE_INVOICE_PAYMENT_SUCCEED = 'invoice.payment_succeeded';

    /**
     * @var Api
     */
    private $api;

    /**
     * @var Debugger
     */
    private $debugger;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var HandlersPool
     */
    private $handlersPool;

    /**
     * @param Api $api
     * @param Debugger $debugger
     * @param DataObjectFactory $dataObjectFactory
     * @param HandlersPool $handlersPool
     */
    public function __construct(
        Api $api,
        Debugger $debugger,
        DataObjectFactory $dataObjectFactory,
        HandlersPool $handlersPool
    ) {
        $this->api = $api;
        $this->debugger = $debugger;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->handlersPool = $handlersPool;
    }

    /**
     * Handle webhook
     *
     * @param object $event
     * @return void
     * @throws \Exception
     */
    public function execute($event)
    {
        $this->debugger->addDebugData('webhook', $event);
        try {
            $event = $this->api->requestRetrieveEvent($event->id);
            $eventType = $event->getType();

            $eventHandler = $this->handlersPool->getHandler($eventType);
            if (!$eventHandler) {
                throw new \Exception(sprintf('Event type %s is unprocessable.', $eventType));
            }
            $eventHandler->execute(
                $this->dataObjectFactory->create($event->getEventObjectData())
            );
        } catch (\Exception $e) {
            $this->debugger
                ->addDebugData('exception', $e->getMessage())
                ->debug();
            throw $e;
        }

        $this->debugger->debug();
    }
}
