<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Authorizenet;

use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\SilentPost as SilentPostProcessor;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Class SilentPost
 * @package Aheadworks\Sarp\Controller\Authorizenet
 */
class SilentPost extends Action
{
    /**
     * @var SilentPostProcessor
     */
    private $silentPost;

    /**
     * @param Context $context
     * @param SilentPostProcessor $silentPost
     */
    public function __construct(
        Context $context,
        SilentPostProcessor $silentPost
    ) {
        parent::__construct($context);
        $this->silentPost = $silentPost;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getPostValue();
                $this->silentPost->process($data);
            } catch (\Exception $e) {
                $this->getResponse()->setHttpResponseCode(500);
            }
        }
    }
}
