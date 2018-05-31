<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Adminhtml\SubscriptionPlan\Edit;

use Aheadworks\Sarp\Api\SubscriptionPlanRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 * @package Aheadworks\Sarp\Block\Adminhtml\SubscriptionPlan\Edit
 */
class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $subscriptionPlanRepository;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param SubscriptionPlanRepositoryInterface $subscriptionPlanRepository
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        SubscriptionPlanRepositoryInterface $subscriptionPlanRepository
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->subscriptionPlanRepository = $subscriptionPlanRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $subscriptionPlanId = $this->request->getParam('subscription_plan_id');
        if ($subscriptionPlanId && $this->subscriptionPlanRepository->get($subscriptionPlanId)) {
            $confirmMessage = __('Are you sure you want to do this?');
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => sprintf(
                    "deleteConfirm('%s', '%s')",
                    $confirmMessage,
                    $this->urlBuilder->getUrl('*/*/delete', ['subscription_plan_id' => $subscriptionPlanId])
                ),
                'sort_order' => 20
            ];
        }
        return $data;
    }
}
