<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Checkout;

use Aheadworks\Sarp\Model\SubscriptionsCart\CheckoutValidator;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package Aheadworks\Sarp\Controller\Checkout
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Persistor
     */
    private $cartPersistor;

    /**
     * @var CheckoutValidator
     */
    private $checkoutValidator;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Persistor $cartPersistor
     * @param CheckoutValidator $checkoutValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Persistor $cartPersistor,
        CheckoutValidator $checkoutValidator
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->cartPersistor = $cartPersistor;
        $this->checkoutValidator = $checkoutValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $cart = $this->cartPersistor->getSubscriptionCart();

        if (!$this->checkoutValidator->isValid($cart)) {
            $messages = $this->checkoutValidator->getMessages();
            $this->messageManager->addNoticeMessage(__(array_pop($messages)));

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('aw_sarp/cart/index');
            return $resultRedirect;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Subscription Checkout'));
        return $resultPage;
    }
}
