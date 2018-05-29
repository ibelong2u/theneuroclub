<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\BuyRequestProcessor;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option as CustomOption;

/**
 * Class ItemsComparator
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 */
class ItemsComparator
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var BuyRequestProcessor
     */
    private $buyRequestProcessor;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param BuyRequestProcessor $buyRequestProcessor
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        BuyRequestProcessor $buyRequestProcessor
    ) {
        $this->productRepository = $productRepository;
        $this->buyRequestProcessor = $buyRequestProcessor;
    }

    /**
     * Check if subscriptions cart items are equals
     *
     * @param SubscriptionsCartItemInterface $item1
     * @param SubscriptionsCartItemInterface $item2
     * @return bool
     */
    public function isEquals(SubscriptionsCartItemInterface $item1, SubscriptionsCartItemInterface $item2)
    {
        $itemProduct1 = $this->getProductToCompare($item1);
        $itemProduct2 = $this->getProductToCompare($item2);

        if ($itemProduct1->getId() != $itemProduct2->getId()) {
            return false;
        }

        $itemProductOptions1 = $itemProduct1->getCustomOptions();
        $itemProductOptions2 = $itemProduct2->getCustomOptions();

        if (!$this->compareProductCustomOptions($itemProductOptions1, $itemProductOptions2)) {
            return false;
        }
        if (!$this->compareProductCustomOptions($itemProductOptions2, $itemProductOptions1)) {
            return false;
        }

        return true;
    }

    /**
     * Get product to compare
     *
     * @param SubscriptionsCartItemInterface $item
     * @return ProductInterface|Product
     * @throws \Exception
     */
    private function getProductToCompare(SubscriptionsCartItemInterface $item)
    {
        $cartCandidates = $this->buyRequestProcessor->getCartCandidates(
            $item->getBuyRequest(),
            $item->getProductId()
        );
        foreach ($cartCandidates as $candidate) {
            if (!$candidate->getParentProductId()) {
                return clone $candidate;
            }
        }
        return $cartCandidates[0];
    }

    /**
     * Compare product custom options
     *
     * @param CustomOption[] $options1
     * @param CustomOption[] $options2
     * @return bool
     */
    private function compareProductCustomOptions($options1, $options2)
    {
        foreach ($options1 as $option) {
            $code = $option->getCode();
            if (in_array($code, ['info_buyRequest'])) {
                continue;
            }
            if (!isset($options2[$code]) || $options2[$code]->getValue() != $option->getValue()) {
                return false;
            }
        }
        return true;
    }
}
