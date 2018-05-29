<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 17/05/2016
 * Time: 15:12
 */

namespace Magenest\Stripe\Model;

use Magenest\Stripe\Model\ResourceModel\SubscriptionInvoice as Resource;
use Magenest\Stripe\Model\ResourceModel\SubscriptionInvoice\Collection as Collection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class SubscriptionInvoice extends AbstractModel
{
    const STATUS_NEW = 0;
    const STATUS_CREATED_ORDER = 1;

    protected $_eventPrefix = 'subscription_invoice_';

    public function __construct(
        Context $context,
        Registry $registry,
        Resource $resource,
        Collection $resourceCollection,
        $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
}
