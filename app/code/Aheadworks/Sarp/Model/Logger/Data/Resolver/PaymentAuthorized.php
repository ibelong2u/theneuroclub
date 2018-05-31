<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Resolver;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class PaymentAuthorized
 * @package Aheadworks\Sarp\Model\Logger\Data\Resolver
 */
class PaymentAuthorized extends BaseResolver
{
    /**
     * {@inheritdoc}
     */
    public function getEntryData($object, array $additionalData = [])
    {
        $data = $this->initEntryData($object);
        $data['title'] = 'Payment authorized';
        if (isset($additionalData['order'])) {
            /** @var OrderInterface $order */
            $order = $additionalData['order'];
            $data['details'] = sprintf(
                'Order #{{orderLink %s %s}} has been created',
                $order->getEntityId(),
                $order->getIncrementId()
            );
        }
        $data['error_details'] = null;
        return $data;
    }
}
