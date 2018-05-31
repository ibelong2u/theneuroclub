<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Product;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterfaceFactory;
use Aheadworks\Sarp\Api\SubscriptionsCartManagementInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor as CartPersistor;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Subscribe
 * @package Aheadworks\Sarp\Controller\Product
 */
class Subscribe extends Action
{
    /**
     * @var SubscriptionsCartManagementInterface
     */
    private $cartManagement;

    /**
     * @var CartPersistor
     */
    private $cartPersistor;

    /**
     * @var SubscriptionsCartItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @param Context $context
     * @param SubscriptionsCartManagementInterface $cartManagement
     * @param CartPersistor $cartPersistor
     * @param SubscriptionsCartItemInterfaceFactory $itemFactory
     */
    public function __construct(
        Context $context,
        SubscriptionsCartManagementInterface $cartManagement,
        CartPersistor $cartPersistor,
        SubscriptionsCartItemInterfaceFactory $itemFactory
    ) {
        parent::__construct($context);
        $this->cartManagement = $cartManagement;
        $this->cartPersistor = $cartPersistor;
        $this->itemFactory = $itemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $params = $this->getRequest()->getParams();

        try {
            $cart = $this->cartPersistor->getSubscriptionCart();
            /** @var SubscriptionsCartItemInterface $cartItem */
            $cartItem = $this->itemFactory->create();
            $cartItem
                ->setProductId($params['product'])
                ->setBuyRequest($this->getBuyRequestSerialized($params));
            if (isset($params['qty'])) {
                $cartItem->setQty($params['qty']);
            }
            $cartItem = $this->cartManagement->add($cart, $cartItem);

            $this->cartPersistor->setCartId($cart->getCartId());

            $this->messageManager->addSuccessMessage(
                __('You added %1 to subscription cart.', $cartItem->getName())
            );
            return $resultJson->setData(
                ['redirectUrl' => $this->_url->getUrl('aw_sarp/cart/index')]
            );
        } catch (LocalizedException $e) {
            $messages = array_unique(explode('\n', $e->getMessage()));
            foreach ($messages as $message) {
                $this->messageManager->addErrorMessage($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to subscription cart right now.')
            );
        }

        return $resultJson->setData([]);
    }

    /**
     * Get buy request serialized
     *
     * @param array $params
     * @return string
     */
    private function getBuyRequestSerialized($params)
    {
        if (isset($params['form_key'])) {
            unset($params['form_key']);
        }
        return serialize($params);
    }
}
