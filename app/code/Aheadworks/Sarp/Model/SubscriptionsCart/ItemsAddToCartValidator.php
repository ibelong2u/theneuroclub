<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\Product\Type\Restrictions as TypeRestrictions;
use Aheadworks\Sarp\Model\Product\SubscribeAbilityChecker;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class ItemsAddToCartValidator
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 */
class ItemsAddToCartValidator extends AbstractValidator
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var TypeRestrictions
     */
    private $typeRestrictions;

    /**
     * @var SubscribeAbilityChecker
     */
    private $subscribeAbilityChecker;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param TypeRestrictions $typeRestrictions
     * @param SubscribeAbilityChecker $subscribeAbilityChecker
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        TypeRestrictions $typeRestrictions,
        SubscribeAbilityChecker $subscribeAbilityChecker
    ) {
        $this->productRepository = $productRepository;
        $this->typeRestrictions = $typeRestrictions;
        $this->subscribeAbilityChecker = $subscribeAbilityChecker;
    }

    /**
     * Returns true if and only if subscriptions cart item is valid for add to cart
     *
     * @param SubscriptionsCartItemInterface $item
     * @return bool
     */
    public function isValid($item)
    {
        $this->_clearMessages();

        if (!\Zend_Validate::is($item->getProductId(), 'NotEmpty')) {
            $this->_addMessages(['Product Id is required.']);
        } else {
            $product = $this->productRepository->getById($item->getProductId());
            $isProductTypeSupported = \Zend_Validate::is(
                $product->getTypeId(),
                'InArray',
                ['haystack' => $this->typeRestrictions->getSupportedProductTypes()]
            );
            if (!$isProductTypeSupported) {
                $this->_addMessages(['Product type %1 isn\'t supported.']);
                return false;
            }

            if (!$this->subscribeAbilityChecker->isSubscribeAvailable($product)) {
                $this->_addMessages(['Subscriptions aren\'t allowed for this product.']);
                return false;
            }
        }
        if (!\Zend_Validate::is($item->getBuyRequest(), 'NotEmpty')) {
            $this->_addMessages(['Buy request is required.']);
        }

        return empty($this->getMessages());
    }
}
