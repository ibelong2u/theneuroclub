<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 02/06/2016
 * Time: 10:57
 */

namespace Magenest\Stripe\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magenest\Stripe\Helper\Data as DataHelper;
use Magento\Framework\Controller\ResultFactory;
use Magenest\Stripe\Model\SubscriptionFactory;

class Cancel extends Action
{
    protected $_helper;

    protected $_subsFactory;

    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        SubscriptionFactory $subscriptionFactory
    ) {
        $this->_helper = $dataHelper;
        $this->_subsFactory = $subscriptionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        /**
         * @var \Magento\Customer\Model\Session $customerSession
         * @var \Magenest\Stripe\Helper\SubscriptionHelper $subscriptionHelper
         */
        $customerSession = $this->_objectManager->create('\Magento\Customer\Model\Session');
        $subscriptionHelper = $this->_objectManager->get('\Magenest\Stripe\Helper\SubscriptionHelper');
        if ($customerSession->isLoggedIn()) {
            $subId = $this->getRequest()->getParam('sub_id');
            $model = $this->_subsFactory->create()->load($subId, "subscription_id");
            $customerId = $model->getData('customer_id');
            if ($customerId == $customerSession->getCustomerId()) {
                $body = $this->_helper->deleteSubscription($subId);
                if (isset($body['id'])) {
                    $this->messageManager->addSuccessMessage(
                        __('Subscription ') . $subId . __(' has been successfully cancelled.')
                    );

                    $model = $subscriptionHelper->updateSubscriptionObjData($model, $body);
                    $model ->save();
                } else {
                    $this->messageManager->addErrorMessage(__('Unable to cancel subscription ') . $subId);
                }
            }
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('stripe/customer/subscription');
    }
}
