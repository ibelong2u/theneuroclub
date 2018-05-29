<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Paypalexpress;

use Magento\Framework\App\Action\Action;

/**
 * Class Cancel
 * @package Aheadworks\Sarp\Controller\Paypalexpress
 */
class Cancel extends Action
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->messageManager->addSuccessMessage(
            __('Express Checkout has been canceled.')
        );
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('aw_sarp/cart/index');
        return $resultRedirect;
    }
}
