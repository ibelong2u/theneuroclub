<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Adminhtml\Subscriptionplan;

use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionPlanInterfaceFactory;
use Aheadworks\Sarp\Api\SubscriptionPlanRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\Page;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Error;
use Magento\Framework\Validator\Exception as ValidatorException;

/**
 * Class Save
 * @package Aheadworks\Sarp\Controller\Adminhtml\Subscriptionplan
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp::subscription_plans';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var PostDataProcessor
     */
    private $postDataProcessor;

    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $subscriptionPlanRepository;

    /**
     * @var SubscriptionPlanInterfaceFactory
     */
    private $subscriptionPlanFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param PostDataProcessor $postDataProcessor
     * @param SubscriptionPlanRepositoryInterface $subscriptionPlanRepository
     * @param SubscriptionPlanInterfaceFactory $subscriptionPlanFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        PostDataProcessor $postDataProcessor,
        SubscriptionPlanRepositoryInterface $subscriptionPlanRepository,
        SubscriptionPlanInterfaceFactory $subscriptionPlanFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->postDataProcessor = $postDataProcessor;
        $this->subscriptionPlanRepository = $subscriptionPlanRepository;
        $this->subscriptionPlanFactory = $subscriptionPlanFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $requestData = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($requestData) {
            $entityData = $this->postDataProcessor->prepareEntityData($requestData);
            try {
                $subscriptionPlan = $this->performSave($entityData);

                $this->dataPersistor->clear('aw_subscription_plan');
                $this->messageManager->addSuccessMessage(__('The subscription plan was successfully saved.'));

                $back = $this->getRequest()->getParam('back');
                if ($back == 'edit') {
                    return $resultRedirect->setPath(
                        '*/*/' . $back,
                        [
                            'subscription_plan_id' => $subscriptionPlan->getSubscriptionPlanId(),
                            '_current' => true
                        ]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (ValidatorException $exception) {
                $this->addValidationMessages($exception);
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while saving the subscription plan.')
                );
            }

            $this->dataPersistor->set('aw_subscription_plan', $entityData);

            if (isset($entityData['subscription_plan_id'])) {
                return $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        'subscription_plan_id' => $entityData['subscription_plan_id'],
                        '_current' => true
                    ]
                );
            }
            return $resultRedirect->setPath('*/*/new', ['_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Perform save
     *
     * @param array $data
     * @return SubscriptionPlanInterface
     */
    private function performSave($data)
    {
        $subscriptionPlanId = isset($data['subscription_plan_id'])
            ? $data['subscription_plan_id']
            : false;
        $subscriptionPlan = $subscriptionPlanId
            ? $this->subscriptionPlanRepository->get($subscriptionPlanId)
            : $this->subscriptionPlanFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $subscriptionPlan,
            $data,
            SubscriptionPlanInterface::class
        );
        return $this->subscriptionPlanRepository->save($subscriptionPlan);
    }

    /**
     * Add validator exceptions message to message collection
     *
     * @param ValidatorException $exception
     * @return void
     */
    private function addValidationMessages(ValidatorException $exception)
    {
        $messages = $exception->getMessages();
        if (empty($messages)) {
            $messages = [$exception->getMessage()];
        }
        foreach ($messages as $message) {
            if (!$message instanceof Error) {
                $message = new Error($message);
            }
            $this->messageManager->addMessage($message);
        }
    }
}
