<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook;

use Magento\Framework\DataObject;

/**
 * Interface HandlerInterface
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\WebHook
 */
interface HandlerInterface
{
    /**
     * Handle webhook event
     *
     * @param DataObject $eventObject
     * @return void
     */
    public function execute(DataObject $eventObject);
}
