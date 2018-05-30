<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Resolver;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class PaymentCaptured
 * @package Aheadworks\Sarp\Model\Logger\Data\Resolver
 */
class PaymentCaptured extends BaseResolver
{
    /**
     * {@inheritdoc}
     */
    public function getEntryData($object, array $additionalData = [])
    {
        $data = $this->initEntryData($object);
        $data['title'] = 'Payment captured';
        if (isset($additionalData['order'])) {
            /** @var OrderInterface $order */
            $order = $additionalData['order'];
            if (isset($additionalData['invoice'])) {
                /** @var InvoiceInterface|Invoice $invoice */
                $invoice = $additionalData['invoice'];
                $data['details'] = sprintf(
                    'Invoice #{{invoiceLink %s %s}} for Order #{{orderLink %s %s}} has been created',
                    $invoice->getEntityId(),
                    $invoice->getIncrementId(),
                    $order->getEntityId(),
                    $order->getIncrementId()
                );
            } else {
                $data['details'] = sprintf(
                    'Invoice for Order #{{orderLink %s %s}} has been created',
                    $order->getEntityId(),
                    $order->getIncrementId()
                );
            }
        }
        $data['error_details'] = null;
        return $data;
    }
}
