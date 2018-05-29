<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Locale\FormatInterface as LocaleFormatInterface;

/**
 * Class ItemsProcessor
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemsProcessor
{
    /**
     * @var SubscriptionsCartItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var BuyRequestProcessor
     */
    private $buyRequestProcessor;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var LocaleFormatInterface
     */
    private $localeFormat;

    /**
     * @param SubscriptionsCartItemInterfaceFactory $itemFactory
     * @param BuyRequestProcessor $buyRequestProcessor
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param LocaleFormatInterface $localeFormat
     */
    public function __construct(
        SubscriptionsCartItemInterfaceFactory $itemFactory,
        BuyRequestProcessor $buyRequestProcessor,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        LocaleFormatInterface $localeFormat
    ) {
        $this->itemFactory = $itemFactory;
        $this->buyRequestProcessor = $buyRequestProcessor;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->localeFormat = $localeFormat;
    }

    /**
     * Process cart item before save
     *
     * @param SubscriptionsCartInterface $cart
     * @param SubscriptionsCartItemInterface $item
     * @return SubscriptionsCartItemInterface[] First element of this array is a parent item
     */
    public function processBeforeAdd(SubscriptionsCartInterface $cart, SubscriptionsCartItemInterface $item)
    {
        $cartItems = $this->splitCartItem($item);
        foreach ($cartItems as $cartItem) {
            $this->filterItemQty($cartItem);
            $this->setItemQtyIfNotSpecified($cart, $cartItem);
        }
        return $cartItems;
    }

    /**
     * Split cart item into parent and child
     *
     * @param SubscriptionsCartItemInterface $item
     * @return array
     * @throws \Exception
     */
    private function splitCartItem(SubscriptionsCartItemInterface $item)
    {
        $resultItems = [];
        $cartCandidates = $this->buyRequestProcessor->getCartCandidates(
            $item->getBuyRequest(),
            $item->getProductId()
        );

        /** @var Product $product */
        foreach ($cartCandidates as $product) {
            /** @var SubscriptionsCartItemInterface $cartItem */
            $cartItem = $this->itemFactory->create();
            $cartItem
                ->setProductId($product->getId())
                ->setName($product->getName())
                ->setSku($product->getSku())
                ->setQty($product->getCartQty())
                ->setBuyRequest($item->getBuyRequest())
                ->setProductOptions(serialize($this->prepareCustomOptions($product)));
            $resultItems[] = $cartItem;
        }
        return $resultItems;
    }

    /**
     * Prepare custom options data
     *
     * @param ProductInterface|Product $product
     * @return array
     */
    private function prepareCustomOptions($product)
    {
        $customOptionsData = [];
        foreach ($product->getCustomOptions() as $customOption) {
            $customOptionsData[] = [
                'code' => $customOption->getCode(),
                'value' => $customOption->getValue()
            ];
        }
        return $customOptionsData;
    }

    /**
     * Filter item qty
     *
     * @param SubscriptionsCartItemInterface $item
     * @return void
     */
    private function filterItemQty(SubscriptionsCartItemInterface $item)
    {
        if ($item->getQty()) {
            $qty = $this->localeFormat->getNumber($item->getQty());
            /** @var ProductInterface $product */
            $product = $this->productRepository->getById($item->getProductId());
            $stockItem = $this->stockRegistry->getStockItem(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            );
            if (!$stockItem->getIsQtyDecimal()) {
                $qty = intval($qty);
            }
            $qty = $qty > 0 ? $qty : 1;
            $item->setQty($qty);
        }
    }

    /**
     * Set item qty if not specified
     *
     * @param SubscriptionsCartInterface $cart
     * @param SubscriptionsCartItemInterface $item
     * @return void
     */
    private function setItemQtyIfNotSpecified(
        SubscriptionsCartInterface $cart,
        SubscriptionsCartItemInterface $item
    ) {
        if (!$item->getQty()) {
            /** @var ProductInterface|Product $product */
            $product = $this->productRepository->getById($item->getProductId());
            $stockItem = $this->stockRegistry->getStockItem(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            );
            $minQty = $stockItem->getMinSaleQty();
            if ($minQty
                && $minQty > 0
                && !$this->isCartContainsProductId($cart, $item->getProductId())
            ) {
                $item->setQty($minQty);
            }
        }
    }

    /**
     * Check if cart contains item with specified product ID
     *
     * @param SubscriptionsCartInterface $cart
     * @param int $productId
     * @return bool
     */
    private function isCartContainsProductId(SubscriptionsCartInterface $cart, $productId)
    {
        /** @var SubscriptionsCartItemInterface $item */
        foreach ($cart->getItems() as $item) {
            if ($item->getProductId() == $productId) {
                return true;
            }
        }
        return false;
    }
}
