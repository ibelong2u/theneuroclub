<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Adminhtml\SubscriptionPlan;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanDescriptionInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Framework\Registry;

/**
 * Class StorefrontDescription
 * @package Aheadworks\Sarp\Block\Adminhtml\SubscriptionPlan
 */
class StorefrontDescription extends \Magento\Backend\Block\Template
{
    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp::subscriptionplan/storefront_description.phtml';

    /**
     * @param Context $context
     * @param SystemStore $systemStore
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        SystemStore $systemStore,
        Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->systemStore = $systemStore;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Get stores options
     *
     * @return array
     */
    public function getStoresOptions()
    {
        return $this->systemStore->getStoreValuesForForm(false, true);
    }

    /**
     * Get storefront descriptions
     *
     * @return array
     */
    public function getDescriptions()
    {
        $descriptions = $this->coreRegistry->registry('aw_subscription_plan_descriptions') ? : [];
        if (count($descriptions) == 0) {
            $descriptions[] = [
                SubscriptionPlanDescriptionInterface::STORE_ID      => 0,
                SubscriptionPlanDescriptionInterface::TITLE         => '',
                SubscriptionPlanDescriptionInterface::DESCRIPTION   => ''
            ];
        }
        return $descriptions;
    }
}
