<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Resolver;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\InvoiceInterface;

/**
 * Class PaymentPaid
 * @package Aheadworks\Sarp\Model\Logger\Data\Resolver
 */
class PaymentPaid extends BaseResolver
{
    /**
     * {@inheritdoc}
     */
    public function getEntryData($object, array $additionalData = [])
    {
        $data = $this->initEntryData($object);
        $data['title'] = 'Payment successful';
        if (isset($additionalData['order']) && isset($additionalData['invoice'])) {
            /** @var OrderInterface $order */
            $order = $additionalData['order'];
            /** @var InvoiceInterface $invoice */
            $invoice = $additionalData['invoice'];
            $data['details'] = sprintf(
                'Order #{{orderLink %s %s}} with Invoice #{{invoiceLink %s %s}} have been created',
                $order->getEntityId(),
                $order->getIncrementId(),
                $invoice->getEntityId(),
                $invoice->getIncrementId()
            );
        }
        $data['error_details'] = null;
        return $data;
    }
}
