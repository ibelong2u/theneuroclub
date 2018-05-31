<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Paypalexpress;

use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ExpressCheckout;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Start
 * @package Aheadworks\Sarp\Controller\Paypalexpress
 */
class Start extends Action
{
    /**
     * @var ExpressCheckout
     */
    private $checkout;

    /**
     * @var Url
     */
    private $url;

    /**
     * @param Context $context
     * @param ExpressCheckout $checkout
     * @param Url $url
     */
    public function __construct(
        Context $context,
        ExpressCheckout $checkout,
        Url $url
    ) {
        parent::__construct($context);
        $this->checkout = $checkout;
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $token = $this->checkout->start();
            if ($token) {
                $this->getResponse()->setRedirect($this->url->getPaypalStartUrl($token));
                return;
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t start Express Checkout.')
            );
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('aw_sarp/cart/index');
        return $resultRedirect;
    }
}
