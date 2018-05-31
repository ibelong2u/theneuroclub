<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Adminhtml\Subscriptionplan;

use Aheadworks\Sarp\Api\SubscriptionPlanRepositoryInterface;
use Magento\Backend\App\Action\Context;

/**
 * Class Delete
 * @package Aheadworks\Sarp\Controller\Adminhtml\Subscriptionplan
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp::subscription_plans';

    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $subscriptionPlanRepository;

    /**
     * @param Context $context
     * @param SubscriptionPlanRepositoryInterface $subscriptionPlanRepository
     */
    public function __construct(
        Context $context,
        SubscriptionPlanRepositoryInterface $subscriptionPlanRepository
    ) {
        parent::__construct($context);
        $this->subscriptionPlanRepository = $subscriptionPlanRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $subscriptionPlanId = (int)$this->getRequest()->getParam('subscription_plan_id');
        if ($subscriptionPlanId) {
            try {
                $this->subscriptionPlanRepository->deleteById($subscriptionPlanId);
                $this->messageManager->addSuccessMessage(__('Subscription plan was successfully deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        $this->messageManager->addErrorMessage(__('Subscription plan could not be deleted.'));
        return $resultRedirect->setPath('*/*/');
    }
}
