<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Adminhtml\Subscriptionplan;

use Aheadworks\Sarp\Api\SubscriptionPlanRepositoryInterface;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionPlan\CollectionFactory;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\Status;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassEnable
 * @package Aheadworks\Sarp\Controller\Adminhtml\Subscriptionplan
 */
class MassEnable extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp::subscription_plans';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $subscriptionPlanRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param SubscriptionPlanRepositoryInterface $subscriptionPlanRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        SubscriptionPlanRepositoryInterface $subscriptionPlanRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->subscriptionPlanRepository = $subscriptionPlanRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            foreach ($collection->getAllIds() as $subscriptionPlanId) {
                $subscriptionPlan = $this->subscriptionPlanRepository->get($subscriptionPlanId);
                $subscriptionPlan->setStatus(Status::ENABLED);
                $this->subscriptionPlanRepository->save($subscriptionPlan);
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been enabled.', $collection->getSize())
            );
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while enable the items.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
