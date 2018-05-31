<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Item\Converter;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ConverterManager as AddressConverterManager;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Address\ItemFactory;

/**
 * Class QuoteAddressItemsConverter
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Item
 */
class QuoteAddressItemsConverter
{
    /**
     * @var ItemFactory
     */
    private $addressItemFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var AddressConverterManager
     */
    private $addressConverterManager;

    /**
     * @param ItemFactory $addressItemFactory
     * @param ProductRepositoryInterface $productRepository
     * @param AddressConverterManager $addressConverterManager
     */
    public function __construct(
        ItemFactory $addressItemFactory,
        ProductRepositoryInterface $productRepository,
        AddressConverterManager $addressConverterManager
    ) {
        $this->addressItemFactory = $addressItemFactory;
        $this->productRepository = $productRepository;
        $this->addressConverterManager = $addressConverterManager;
    }

    /**
     * Convert to quote address items
     *
     * @param SubscriptionsCartItemInterface[] $items
     * @param SubscriptionsCartAddressInterface $address
     * @param bool $useTrialPrice
     * @return AddressItem[]
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function convert(array $items, $address = null, $useTrialPrice = false)
    {
        $addressItems = [];
        $parentAddressItem = null;
        foreach ($items as $item) {
            /** @var AddressItem $addressItem */
            $addressItem = $this->addressItemFactory->create();
            /** @var ProductInterface|Product $product */
            $product = $this->productRepository->getById($item->getProductId());
            $addressItem
                ->setProductId($product->getId())
                ->setProduct($product)
                ->setSku($product->getSku())
                ->setName($product->getName())
                ->setWeight($product->getWeight())
                ->setPrice(
                    $useTrialPrice ? $item->getTrialPrice() : $item->getRegularPrice()
                )
                ->setBasePrice(
                    $useTrialPrice ? $item->getBaseTrialPrice() : $item->getBaseRegularPrice()
                )
                ->setQty($item->getQty());

            if (!$item->getParentItemId()) {
                $parentAddressItem = $addressItem;
            }
            if ($parentAddressItem && $item->getParentItemId()) {
                $addressItem->setParentItem($parentAddressItem);
            }

            if ($address) {
                $quoteAddress = $this->addressConverterManager->toQuoteAddress($address);
                $addressItem->setAddress($quoteAddress);
            }

            $addressItem->setAssociatedSubscriptionItem($item);
            $addressItems[] = $addressItem;
        }

        return $addressItems;
    }
}
