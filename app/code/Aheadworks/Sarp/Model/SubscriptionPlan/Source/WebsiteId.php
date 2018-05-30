<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionPlan\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\ResourceModel\Website\Collection;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

/**
 * Class WebsiteId
 * @package Aheadworks\Sarp\Model\SubscriptionPlan\Source
 */
class WebsiteId implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $websiteCollectionFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * @param CollectionFactory $websiteCollectionFactory
     */
    public function __construct(CollectionFactory $websiteCollectionFactory)
    {
        $this->websiteCollectionFactory = $websiteCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            /** @var Collection $websiteCollection */
            $websiteCollection = $this->websiteCollectionFactory->create();
            $this->options = $websiteCollection->toOptionArray();
        }
        return $this->options;
    }
}
