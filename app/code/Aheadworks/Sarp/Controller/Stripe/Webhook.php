<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Stripe;

use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook\Handler;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Class Webhook
 * @package Aheadworks\Sarp\Controller\Stripe
 */
class Webhook extends Action
{
    /**
     * @var Handler
     */
    private $webhookHandler;

    /**
     * @param Context $context
     * @param Handler $webhookHandler
     */
    public function __construct(
        Context $context,
        Handler $webhookHandler
    ) {
        parent::__construct($context);
        $this->webhookHandler = $webhookHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $event = \Zend_Json::decode(@file_get_contents("php://input"), \Zend_Json::TYPE_OBJECT);
                $this->webhookHandler->execute($event);
                $this->getResponse()->setHttpResponseCode(200);
            } catch (\Exception $e) {
                $this->getResponse()->setHttpResponseCode(500);
            }
        }
    }
}
