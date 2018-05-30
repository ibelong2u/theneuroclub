<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Item;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Item
 */
class Validator extends AbstractValidator
{
    /**
     * @var QuantityValidator
     */
    private $quantityValidator;

    /**
     * @param QuantityValidator $quantityValidator
     */
    public function __construct(QuantityValidator $quantityValidator)
    {
        $this->quantityValidator = $quantityValidator;
    }

    /**
     * Returns true if and only if subscription cart item entity meets the validation requirements
     *
     * @param SubscriptionsCartItemInterface $item
     * @return bool
     */
    public function isValid($item)
    {
        $this->_clearMessages();

        if (!\Zend_Validate::is($item->getName(), 'NotEmpty')) {
            $this->_addMessages(['Name is required.']);
        }
        if (!\Zend_Validate::is($item->getBuyRequest(), 'NotEmpty')) {
            $this->_addMessages(['Buy request is required.']);
        } elseif (!$this->quantityValidator->isValid($item)) {
            $this->_addMessages($this->quantityValidator->getMessages());
        }

        return empty($this->getMessages());
    }
}
