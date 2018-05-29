<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Adminhtml\Subscriptionplan;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterfaceFactory;
use Aheadworks\Sarp\Api\SubscriptionPlanRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit
 * @package Aheadworks\Sarp\Controller\Adminhtml\Subscriptionplan
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp::subscription_plans';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var SubscriptionPlanInterfaceFactory
     */
    private $subscriptionPlanFactory;

    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $subscriptionPlanRepository;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SubscriptionPlanInterfaceFactory $subscriptionPlanFactory
     * @param SubscriptionPlanRepositoryInterface $subscriptionPlanRepository
     * @param Registry $coreRegistry
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SubscriptionPlanInterfaceFactory $subscriptionPlanFactory,
        SubscriptionPlanRepositoryInterface $subscriptionPlanRepository,
        Registry $coreRegistry,
        DataObjectProcessor $dataObjectProcessor,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->subscriptionPlanFactory = $subscriptionPlanFactory;
        $this->subscriptionPlanRepository = $subscriptionPlanRepository;
        $this->coreRegistry = $coreRegistry;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $subscriptionPlanId = (int)$this->getRequest()->getParam('subscription_plan_id');
        /** @var SubscriptionPlanInterface $subscriptionPlan */
        $subscriptionPlan = $this->subscriptionPlanFactory->create();
        if ($subscriptionPlanId) {
            try {
                $subscriptionPlan = $this->subscriptionPlanRepository->get($subscriptionPlanId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while editing the subscription plan.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
        }

        $this->registerSubscriptionPlanDescriptionsData($subscriptionPlan);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage
            ->setActiveMenu('Aheadworks_Sarp::subscription_plans')
            ->getConfig()->getTitle()->prepend(
                $subscriptionPlanId ?  __('Edit Subscription Plan') : __('New Subscription Plan')
            );
        return $resultPage;
    }

    /**
     * Register subscription plan description data
     *
     * @param SubscriptionPlanInterface $subscriptionPlan
     * @return void
     */
    private function registerSubscriptionPlanDescriptionsData(SubscriptionPlanInterface $subscriptionPlan)
    {
        $subscriptionPlanData = $this->dataPersistor->get('aw_subscription_plan')
            ? $this->dataPersistor->get('aw_subscription_plan')
            : $this->dataObjectProcessor->buildOutputDataArray(
                $subscriptionPlan,
                SubscriptionPlanInterface::class
            );
        $subscriptionPlanDescriptionsData = isset($subscriptionPlanData['descriptions'])
            ? $subscriptionPlanData['descriptions']
            : [];
        $this->coreRegistry->register('aw_subscription_plan_descriptions', $subscriptionPlanDescriptionsData);
    }
}
