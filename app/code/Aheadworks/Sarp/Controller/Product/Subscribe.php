<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Product;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterfaceFactory;
use Aheadworks\Sarp\Api\SubscriptionsCartManagementInterface;
use Aheadworks\Sarp\Model\ResourceModel\SubscriptionsCart\ItemRepository;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor as CartPersistor;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
//use Quinoid\Subscription\Helper\Data;

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

    protected $helper;

    protected $bundleHelper;

    protected $jsonHelper;

    protected $cartItemRepo;

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
        SubscriptionsCartItemInterfaceFactory $itemFactory,
        ItemRepository $cartItemRepo,
        \Quinoid\Subscription\Helper\Data $helper,
        \Quinoid\Subscription\Helper\BundleCollection $bundleHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct($context);
        $this->cartManagement = $cartManagement;
        $this->cartPersistor  = $cartPersistor;
        $this->itemFactory    = $itemFactory;
        $this->helper         = $helper;
        $this->bundleHelper   = $bundleHelper;
        $this->jsonHelper     = $jsonHelper;
        $this->cartItemRepo   =  $cartItemRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $params = $this->getRequest()->getParams();
        $result ='';
        try {
            $cart = $this->cartPersistor->getSubscriptionCart();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $logger = $objectManager->get("Psr\Log\LoggerInterface");
            $logger->info($params['form_key'],$params);
           
            
            /*===== Additional code for the customisation of bundling items =====*/
            $cartId = $cart->getCartId();
            if ($cartId != '' && isset($cartId)) {
                $result = $this->getBundledItem($params['product'],$cart->getCartId(),$params['form_key']);
                if(isset($result)){
                    $logger->info(" Parent simple = ". $result);
                    $resultData = $this->jsonHelper->jsonDecode($result);
                    $params   = $resultData['params'];
                    //Existing cart items to be deleted 
                    $deleting = $resultData['deleted'];
                    if(count($deleting)>0){
                        foreach($deleting as $pid => $itemid) { 
                            $logger->info('result'.$itemid);
                            $this->cartItemRepo->deleteById($cartId,$itemid); 
                        }
                    }
                    
                }
            }
            /*==== End of Additional code ===*/
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
