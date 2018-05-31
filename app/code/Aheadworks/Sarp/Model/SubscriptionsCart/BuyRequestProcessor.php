<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Quote\Model\Quote\Item\Option as QuoteItemOption;

/**
 * Class BuyRequestProcessor
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 */
class BuyRequestProcessor
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductType
     */
    private $productType;

    /**
     * @var DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var array
     */
    private $cartCandidatesCache = [];

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ProductType $productType
     * @param DataObjectFactory $objectFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductType $productType,
        DataObjectFactory $objectFactory
    ) {
        $this->productRepository = $productRepository;
        $this->productType = $productType;
        $this->objectFactory = $objectFactory;
    }

    /**
     * Set qty value to buy request string
     *
     * @param string $buyRequest
     * @param float $qty
     * @return string
     */
    public function setQty($buyRequest, $qty)
    {
        $buyRequestData = unserialize($buyRequest);
        $buyRequestData['qty'] = $qty;
        return serialize($buyRequestData);
    }

    /**
     * Get cart candidates
     *
     * @param string $buyRequest
     * @param int $productId
     * @return array
     * @throws \Exception
     */
    public function getCartCandidates($buyRequest, $productId)
    {
        $hash = md5($buyRequest);
        if (!isset($this->cartCandidatesCache[$hash])) {
            /** @var ProductInterface|Product $product */
            $product = $this->productRepository->getById($productId, false, null, true);
            $productTypeInstance = $this->productType->factory($product);
            $buyRequestObject = $this->objectFactory->create(unserialize($buyRequest));

            $cartCandidates = $productTypeInstance->prepareForCartAdvanced(
                $buyRequestObject,
                $product,
                \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
            );
            if (is_string($cartCandidates) || $cartCandidates instanceof \Magento\Framework\Phrase) {
                throw new \Exception(strval($cartCandidates));
            }
            if (!is_array($cartCandidates)) {
                $cartCandidates = [$cartCandidates];
            }

            $this->cartCandidatesCache[$hash] = $cartCandidates;
        }
        return $this->cartCandidatesCache[$hash];
    }

    /**
     * Get quantity options
     *
     * @param string $buyRequest
     * @param int $productId
     * @return OptionInterface[]|QuoteItemOption[]
     * @throws \Exception
     */
    public function getQtyOptions($buyRequest, $productId)
    {
        $qtyOptions = [];
        $cartCandidates = $this->getCartCandidates($buyRequest, $productId);
        /** @var Product $product */
        foreach ($cartCandidates as $product) {
            if (!$product->getParentProductId()) {
                $products = [];
                $optionsByCode = [];
                foreach ($product->getCustomOptions() as $customOption) {
                    /** @var $customOption OptionInterface|QuoteItemOption */
                    $product = $customOption->getProduct();
                    if (is_object($product)) {
                        $optionsProductId = $product->getId();
                        if ($optionsProductId && $optionsProductId != $productId) {
                            $products[$optionsProductId] = $product;
                        }
                    }
                    $optionsByCode[$customOption->getCode()] = $customOption;
                }
                foreach (array_keys($products) as $optionProductId) {
                    $code = 'product_qty_' . $optionProductId;
                    if (isset($optionsByCode[$code])) {
                        /** @var $option OptionInterface|QuoteItemOption */
                        $option = $optionsByCode[$code];
                        if (!$option->getProduct()) {
                            $option->setProduct($products[$optionProductId]);
                        }
                        $qtyOptions[] = $optionsByCode[$code];
                    }
                }
            }
        }
        return $qtyOptions;
    }
}
