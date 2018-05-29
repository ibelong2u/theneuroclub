<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Paypalexpress;

use Aheadworks\Sarp\Api\SubscriptionsCartManagementInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ExpressCheckout;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ReturnAction
 * @package Aheadworks\Sarp\Controller\Paypalexpress
 */
class ReturnAction extends Action
{
    /**
     * @var SubscriptionsCartManagementInterface
     */
    private $cartManagement;

    /**
     * @var Persistor
     */
    private $cartPersistor;

    /**
     * @var ExpressCheckout
     */
    private $checkout;

    /**
     * @param Context $context
     * @param SubscriptionsCartManagementInterface $cartManagement
     * @param Persistor $cartPersistor
     * @param ExpressCheckout $checkout
     */
    public function __construct(
        Context $context,
        SubscriptionsCartManagementInterface $cartManagement,
        Persistor $cartPersistor,
        ExpressCheckout $checkout
    ) {
        parent::__construct($context);
        $this->cartManagement = $cartManagement;
        $this->cartPersistor = $cartPersistor;
        $this->checkout = $checkout;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $token = $this->getRequest()->getParam('token');
        if ($token) {
            try {
                $this->checkout->updateCart($token);
                $cartId = $this->cartPersistor->getCartId();
                $this->cartManagement->submit($cartId, ['token' => $token]);
                $resultRedirect->setPath('aw_sarp/checkout/success');
                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t process Express Checkout recurring profile creation.')
                );
            }
        }

        $resultRedirect->setPath('aw_sarp/cart/index');
        return $resultRedirect;
    }
}
