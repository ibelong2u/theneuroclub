<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Adminhtml\Log;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Aheadworks\Sarp\Model\ResourceModel\Logger\LogEntry\CollectionFactory;

/**
 * Class Clear
 * @package Aheadworks\Sarp\Controller\Adminhtml\Log
 */
class Clear extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp::log';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $logRecords = $this->collectionFactory->create();
            $logRecords->walk('delete');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }
}
