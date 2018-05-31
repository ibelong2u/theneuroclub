<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Order\Item;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;
use Aheadworks\Sarp\Model\Profile\PaymentInfo;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option as CustomOption;
use Magento\Catalog\Model\Product\Configuration\Item\OptionFactory as CustomOptionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Magento\Framework\DataObject\Copy;

/**
 * Class Converter
 * @package Aheadworks\Sarp\Model\Order\Item
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Converter
{
    /**
     * @var OrderItemInterfaceFactory
     */
    private $orderItemFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomOptionFactory
     */
    private $customOptionFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param OrderItemInterfaceFactory $orderItemFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CustomOptionFactory $customOptionFactory
     * @param Copy $objectCopyService
     */
    public function __construct(
        OrderItemInterfaceFactory $orderItemFactory,
        ProductRepositoryInterface $productRepository,
        CustomOptionFactory $customOptionFactory,
        Copy $objectCopyService
    ) {
        $this->orderItemFactory = $orderItemFactory;
        $this->productRepository = $productRepository;
        $this->customOptionFactory = $customOptionFactory;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Convert from subscription profile item
     *
     * @param ProfileItemInterface $item
     * @param ProfilePaymentInfoInterface $paymentInfo
     * @param ProfileInterface $profile
     * @return OrderItemInterface[]
     */
    public function fromProfileItem(
        ProfileItemInterface $item,
        ProfilePaymentInfoInterface $paymentInfo,
        ProfileInterface $profile
    ) {
        $paymentType = $paymentInfo->getPaymentType();
        $storeId = $profile->getStoreId();

        $orderItems = [];

        /** @var OrderItemInterface $parentOrderItem */
        $parentOrderItem = $this->orderItemFactory->create();
        $this->initOrderItem($parentOrderItem, $item, $paymentType, $storeId);
        $orderItems[] = $parentOrderItem;

        foreach ($this->getChildItems($item->getItemId(), $profile) as $childItem) {
            /** @var OrderItemInterface $childOrderItem */
            $childOrderItem = $this->orderItemFactory->create();
            $this->initOrderItem($childOrderItem, $childItem, $paymentType, $storeId);
            $childOrderItem->setParentItem($parentOrderItem);
            $orderItems[] = $childOrderItem;
        }

        return $orderItems;
    }

    /**
     * Convert from payment info as initial fee item
     *
     * @param ProfilePaymentInfoInterface $paymentInfo
     * @param $storeId
     * @return OrderItemInterface
     */
    public function fromPaymentInfoAsInitial(ProfilePaymentInfoInterface $paymentInfo, $storeId)
    {
        /** @var OrderItemInterface $orderItem */
        $orderItem = $this->orderItemFactory->create();
        $orderItem
            ->setName(__('Recurring Profile Initial Fee'))
            ->setDescription('')
            ->setSku('initial_fee')
            ->setStoreId($storeId)
            ->setIsVirtual(true)
            ->setProductType(ProductType::TYPE_VIRTUAL)
            ->setWeight(0)
            ->setQtyOrdered(1)
            ->setPrice($paymentInfo->getAmount())
            ->setBasePrice($paymentInfo->getBaseAmount())
            ->setPriceInclTax($paymentInfo->getAmount())
            ->setBasePriceInclTax($paymentInfo->getBaseAmount())
            ->setOriginalPrice($paymentInfo->getAmount())
            ->setBaseOriginalPrice($paymentInfo->getBaseAmount())
            ->setRowTotal($paymentInfo->getAmount())
            ->setBaseRowTotal($paymentInfo->getBaseAmount())
            ->setRowTotalInclTax($paymentInfo->getAmount())
            ->setBaseRowTotalInclTax($paymentInfo->getBaseAmount());

        return $orderItem;
    }

    /**
     * Init order item
     *
     * @param OrderItemInterface $orderItem
     * @param ProfileItemInterface $profileItem
     * @param string $paymentType
     * @param int $storeId
     * @return OrderItemInterface
     */
    private function initOrderItem($orderItem, $profileItem, $paymentType, $storeId)
    {
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_profile_item',
            'to_order_item',
            $profileItem,
            $orderItem
        );
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_convert_profile_item',
            'to_order_item_' . $paymentType,
            $profileItem,
            $orderItem
        );

        /** @var ProductInterface|Product $product */
        $product = $this->productRepository->getById($profileItem->getProductId());

        $orderItem
            ->setStoreId($storeId)
            ->setProductId($product->getId())
            ->setSku($product->getSku())
            ->setWeight($product->getWeight())
            ->setIsVirtual($product->getIsVirtual())
            ->setName($this->getItemName($product, $paymentType))
            ->setProductType($product->getTypeId())
            ->setOriginalPrice($orderItem->getPrice())
            ->setBaseOriginalPrice($orderItem->getBasePrice());
        $product
            ->setFinalPrice(null)
            ->setCustomOptions($this->prepareProductCustomOptions($profileItem->getProductOptions()));
        $orderItem->setProductOptions(
            $product->getTypeInstance()->getOrderOptions($product)
        );

        return $orderItem;
    }

    /**
     * Get order item name
     *
     * @param Product $product
     * @param string $paymentType
     * @return string
     */
    private function getItemName($product, $paymentType)
    {
        $itemName = $product->getName();

        switch ($paymentType) {
            case PaymentInfo::PAYMENT_TYPE_INITIAL:
                $itemName = __('Recurring Profile Initial Fee');
                break;
            case PaymentInfo::PAYMENT_TYPE_TRIAL:
                $itemName = __('Trial ') . $product->getName();
                break;
            case PaymentInfo::PAYMENT_TYPE_REGULAR:
            default:
                break;
        }

        return $itemName;
    }

    /**
     * Prepare product custom options
     *
     * @param string $productOptions
     * @return CustomOption[]
     */
    private function prepareProductCustomOptions($productOptions)
    {
        $customOptions = [];
        $productOptions = unserialize($productOptions);
        foreach ($productOptions as $option) {
            $code = $option['code'];
            /** @var CustomOption $customOption */
            $customOption = $this->customOptionFactory->create();
            $customOption
                ->setCode($code)
                ->setValue($option['value']);
            if ($code == 'simple_product') {
                $customOption->setProduct($this->productRepository->getById($option['value']));
            }
            $customOptions[$code] = $customOption;
        }
        return $customOptions;
    }

    /**
     * Get child profile items
     *
     * @param int $itemId
     * @param ProfileInterface $profile
     * @return ProfileItemInterface[]
     */
    private function getChildItems($itemId, $profile)
    {
        $childItems = [];
        foreach ($profile->getInnerItems() as $item) {
            if ($item->getParentItemId() == $itemId) {
                $childItems[] = $item;
            }
        }
        return $childItems;
    }
}
