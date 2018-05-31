<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Observer;

use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor as CartPersistor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ClearSessionObserver
 * @package Aheadworks\Sarp\Observer
 */
class ClearSessionObserver implements ObserverInterface
{
    /**
     * @var CartPersistor
     */
    private $cartPersistor;

    /**
     * @param CartPersistor $cartPersistor
     */
    public function __construct(CartPersistor $cartPersistor)
    {
        $this->cartPersistor = $cartPersistor;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $this->cartPersistor->clear();
    }
}
