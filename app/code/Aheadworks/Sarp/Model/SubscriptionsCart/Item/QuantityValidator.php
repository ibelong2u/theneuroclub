<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Item;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\BuyRequestProcessor;
use Aheadworks\Sarp\Model\SubscriptionsCart\Item\QuantityValidator\ItemQtyList;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Model\StockState;
use Magento\Framework\DataObject;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Quote\Model\Quote\Item\Option as QuoteItemOption;

/**
 * Class Validator
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Item
 */
class QuantityValidator extends AbstractValidator
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var StockStateInterface|StockState
     */
    private $stockState;

    /**
     * @var BuyRequestProcessor
     */
    private $buyRequestProcessor;

    /**
     * @var ItemQtyList
     */
    private $itemQtyList;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param StockStateInterface $stockState
     * @param BuyRequestProcessor $buyRequestProcessor
     * @param ItemQtyList $itemQtyList
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        StockStateInterface $stockState,
        BuyRequestProcessor $buyRequestProcessor,
        ItemQtyList $itemQtyList
    ) {
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->stockState = $stockState;
        $this->buyRequestProcessor = $buyRequestProcessor;
        $this->itemQtyList = $itemQtyList;
    }

    /**
     * Returns true if and only if subscription cart item quantity meets the validation requirements
     *
     * @param SubscriptionsCartItemInterface $item
     * @return bool
     */
    public function isValid($item)
    {
        $this->_clearMessages();

        $qty = $item->getQty();
        if (!\Zend_Validate::is($qty, 'NotEmpty')) {
            $this->_addMessages(['Qty is required.']);
        } elseif (!is_numeric($qty) || $qty <= 0) {
            $this->_addMessages(['Qty is incorrect.']);
        } else {
            $productId = $item->getProductId();
            /** @var Product $product */
            $product = $this->productRepository->getById($productId);
            $websiteId = $product->getStore()->getWebsiteId();

            $stockItem = $this->stockRegistry->getStockItem($productId, $websiteId);
            $stockItem->setProductName($product->getName());
            if (!$stockItem instanceof StockItemInterface) {
                $this->_addMessages(['The stock item for Product is not valid.']);
            } else {
                $stockStatus = $this->stockRegistry->getStockStatus($productId, $websiteId);
                if ($stockStatus && $stockStatus->getStockStatus() === Stock::STOCK_OUT_OF_STOCK) {
                    $this->_addMessages(['This product is out of stock.']);
                } else {
                    $qtyOptions = $this->buyRequestProcessor->getQtyOptions($item->getBuyRequest(), $productId);
                    if ($qtyOptions) {
                        $qty = $product->getTypeInstance()->prepareQuoteItemQty($qty, $product);
                        if ($stockStatus) {
                            $result = $this->stockState->checkQtyIncrements(
                                $productId,
                                $qty,
                                $websiteId
                            );
                            $this->processQtyCheckResult($result);
                        }
                        foreach ($qtyOptions as $option) {
                            $result = $this->checkQtyForOption($item, $qty, $option);
                            $this->processQtyCheckResult($result);
                        }
                    } elseif (!$item->getParentItemId()) {
                        $qtyToCheck = $this->itemQtyList->getQty(
                            $productId,
                            $item->getItemId(),
                            $item->getCartId(),
                            $item->getParentItemId() ? 0 : $qty
                        );
                        /** @var DataObject $result */
                        $result = $this->stockState->checkQuoteItemQty(
                            $productId,
                            $qty,
                            $qtyToCheck,
                            $qty,
                            $websiteId
                        );
                        $this->processQtyCheckResult($result);
                    }
                }
            }
        }

        return empty($this->getMessages());
    }

    /**
     * Process quantity check result
     *
     * @param DataObject $result
     * @return void
     */
    private function processQtyCheckResult(DataObject $result)
    {
        if ($result->getHasError()) {
            $this->_addMessages([$result->getMessage()]);
        }
    }

    /**
     * Check quantity for option
     *
     * @param SubscriptionsCartItemInterface $item
     * @param float $qty
     * @param OptionInterface|QuoteItemOption $option
     * @return DataObject
     */
    private function checkQtyForOption(SubscriptionsCartItemInterface $item, $qty, $option)
    {
        $product = $option->getProduct();
        $productId = $product->getId();
        $websiteId = $product->getStore()->getWebsiteId();

        $optionValue = $option->getValue();
        $optionQty = $qty * $optionValue;

        $optionStockItem = $this->stockRegistry->getStockItem($productId, $websiteId);
        $optionStockItem->setProductName($product->getName());
        $optionStockItem->setIsChildItem(true);
        $optionStockItem->setSuppressCheckQtyIncrements(true);

        $qtyForCheck = $this->itemQtyList->getQty(
            $productId,
            $item->getItemId(),
            $item->getCartId(),
            $optionQty
        );
        /** @var DataObject $result */
        $result = $this->stockState->checkQuoteItemQty(
            $productId,
            $optionQty,
            $qtyForCheck,
            $optionValue,
            $websiteId
        );
        $optionStockItem->unsIsChildItem();

        return $result;
    }
}
