<?php
/**
 * Created by PhpStorm.
 * User: thaivh
 * Date: 28/3/17
 * Time: 09:04
 */

namespace Magenest\Stripe\Controller\Customer;

use Magenest\Stripe\Controller\Customer\Card as Card;
use Magento\Framework\Controller\ResultFactory;

class Del extends Card
{
    public function execute()
    {
        /**
         * @var \Magento\Customer\Model\Session $customerSession
         * @var \Magenest\Stripe\Helper\SubscriptionHelper $subscriptionHelper
         * @var \Magento\Framework\Controller\Result\Redirect $resultRedirect
         */
        $customerSession = $this->_objectManager->create('\Magento\Customer\Model\Session');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getCustomerId();
            $id = $this->getRequest()->getParam('id');
            $cardModel = $this->_cardFactory->create();
            $data = $cardModel->load($id);

            $cardId = $data->getData('card_id');
            $tableMagentoCustomerId = $data->getData('magento_customer_id');
            if ($tableMagentoCustomerId == $customerId) {
                $customer = $this->_customerFactory->create()->load($tableMagentoCustomerId, 'magento_customer_id');
                $stripeCustomerId = $customer->getData('stripe_customer_id');
                $response = $this->_helper->deleteCard($stripeCustomerId, $cardId);
                if (isset($response['id'])) {
                    //delete card success -> delete from db
                    $cardModel->delete();
                    $this->messageManager->addSuccessMessage("Delete card success");
                }
            }
            return $resultRedirect->setPath('stripe/customer/card');
        }
    }
}
