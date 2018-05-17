<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 16/03/2017
 * Time: 00:24
 */

namespace Magenest\Stripe\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;
use Magenest\Stripe\Model\SubscriptionFactory;

class MassUpdate extends \Magenest\Stripe\Controller\Adminhtml\Subscription
{
    protected $_filter;
    protected $subscriptionHelper;

    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory,
        SubscriptionFactory $subscriptionFactory,
        LoggerInterface $loggerInterface,
        Registry $registry,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magenest\Stripe\Helper\SubscriptionHelper $subscriptionHelper
    ) {
        parent::__construct($context, $pageFactory, $subscriptionFactory, $loggerInterface, $registry);
        $this->_filter = $filter;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_subscriptionFactory->create()->getCollection());
        $count = 0;
        if ($collection) {
            foreach ($collection as $item) {
                $subscriptionId = $item->getData('subscription_id');
                $subscriptionResponse = $this->subscriptionHelper->getSubscriptionData($subscriptionId);
                if (isset($subscriptionResponse['id'])) {
                    $this->subscriptionHelper->updateSubscriptionObjData($item, $subscriptionResponse);
                    $item->save();
                    $count++;
                }
            }
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 subscription(s) have been update.', $count)
        );

        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
