<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 17/05/2016
 * Time: 15:12
 */

namespace Magenest\Stripe\Model;

use Magenest\Stripe\Model\ResourceModel\SubscriptionItem as Resource;
use Magenest\Stripe\Model\ResourceModel\SubscriptionItem\Collection as Collection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class SubscriptionItem extends AbstractModel
{
    protected $_eventPrefix = 'subscription_item_';

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
