<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ExpressCheckout;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\TotalsCollector;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class CartValidator
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ExpressCheckout
 */
class CartValidator extends AbstractValidator
{
    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @param TotalsCollector $totalsCollector
     */
    public function __construct(TotalsCollector $totalsCollector)
    {
        $this->totalsCollector = $totalsCollector;
    }

    /**
     * Returns true if and only if subscriptions cart is valid for start express checkout
     *
     * @param SubscriptionsCartInterface $cart
     * @return bool
     */
    public function isValid($cart)
    {
        $this->_clearMessages();
        $this->totalsCollector->collect($cart);

        if (!\Zend_Validate::is($cart->getItems(), 'NotEmpty')) {
            $this->_addMessages(['Subscription cart is empty.']);
        }
        if (!\Zend_Validate::is($cart->getGrandTotal(), 'GreaterThan', ['min' => 0])) {
            $this->_addMessages(
                [
                    'PayPal can\'t process subscriptions with a zero balance due. '
                    . 'To finish your purchase, please go through the subscription checkout process.'
                ]
            );
        }

        return empty($this->getMessages());
    }
}
