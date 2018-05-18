<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 02/06/2016
 * Time: 10:57
 */

namespace Magenest\Stripe\Controller\Adminhtml\Subscription;

use Magenest\Stripe\Controller\Adminhtml\Subscription;
use Magento\Backend\App\Action;
use Magenest\Stripe\Helper\Data as DataHelper;
use Magento\Framework\Controller\ResultFactory;
use Magenest\Stripe\Model\SubscriptionFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class Cancel extends Subscription
{
    protected $stripehelper;

    protected $_subsFactory;

    public function __construct(
        Action\Context $context,
        DataHelper $dataHelper,
        SubscriptionFactory $subscriptionFactory,
        PageFactory $pageFactory,
        LoggerInterface $loggerInterface,
        Registry $registry
    ) {
        $this->stripehelper = $dataHelper;
        $this->_subsFactory = $subscriptionFactory;
        parent::__construct($context, $pageFactory, $subscriptionFactory, $loggerInterface, $registry);
    }

    public function execute()
    {
        /**
         * @var \Magento\Customer\Model\Session $customerSession
         * @var \Magenest\Stripe\Helper\SubscriptionHelper $subscriptionHelper
         */
        $subscriptionHelper = $this->_objectManager->get('\Magenest\Stripe\Helper\SubscriptionHelper');
        $subId = $this->getRequest()->getParam('sub_id');
        $model = $this->_subsFactory->create()->load($subId, "subscription_id");
        $body = $this->stripehelper->deleteSubscription($subId);
        if ($body['id']) {
            $this->messageManager->addSuccessMessage(
                __('Subscription ') . $subId . __(' has been successfully cancelled.')
            );

            $model = $subscriptionHelper->updateSubscriptionObjData($model, $body);
            $model->save();
        } else {
            $this->messageManager->addErrorMessage(__('Unable to cancel subscription ') . $subId);
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('stripe/subscription/view', ['id'=>$model->getData('id')]);
    }
}
