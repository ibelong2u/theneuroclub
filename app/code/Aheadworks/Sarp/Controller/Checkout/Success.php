<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Checkout;

use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Aheadworks\Sarp\Model\SubscriptionsCart\SuccessValidator;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Success
 * @package Aheadworks\Sarp\Controller\Checkout
 */
class Success extends Action
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
     * @var SuccessValidator
     */
    private $successValidator;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Persistor $cartPersistor
     * @param SuccessValidator $successValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Persistor $cartPersistor,
        SuccessValidator $successValidator
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->cartPersistor = $cartPersistor;
        $this->successValidator = $successValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->successValidator->isValid()) {
            $this->cartPersistor->clear();
            $resultPage = $this->resultPageFactory->create();
            return $resultPage;
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('aw_sarp/cart/index');
        return $resultRedirect;
    }
}
