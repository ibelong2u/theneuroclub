<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Ui\Component\Listing\Column\Subscriptions;

use Aheadworks\Sarp\Ui\Component\Listing\Column\Link;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderId
 * @package Aheadworks\Sarp\Ui\Component\Listing\Column\Subscriptions
 */
class OrderId extends Link
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $url
     * @param OrderRepositoryInterface $orderRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $url,
        OrderRepositoryInterface $orderRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $url,
            $components,
            $data
        );
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareLinkText(array $item)
    {
        $orderId = $item[$this->getName()];
        $order = $this->orderRepository->get($orderId);
        return '#' . $order->getIncrementId();
    }
}
