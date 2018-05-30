<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class Validator
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 */
class Validator extends AbstractValidator
{
    /**
     * @var DateChecker
     */
    private $dateChecker;

    /**
     * @param DateChecker $dateChecker
     */
    public function __construct(
        DateChecker $dateChecker
    ) {
        $this->dateChecker = $dateChecker;
    }

    /**
     * Returns true if and only if subscription cart entity meets the validation requirements
     *
     * @param SubscriptionsCartInterface $cart
     * @return bool
     */
    public function isValid($cart)
    {
        $this->_clearMessages();

        if ($cart->getStartDate()) {
            if (!\Zend_Validate::is($cart->getStartDate(), 'Date')) {
                $this->_addMessages(['Start date is incorrect.']);
            } else {
                $currentDate = $this->dateChecker->getCurrentDate();
                $startDate = new \Zend_Date($cart->getStartDate(), DateTime::DATE_INTERNAL_FORMAT, 'en_US');

                if ($startDate->isEarlier($currentDate)) {
                    $this->_addMessages(['Start date must be in future.']);
                }
            }
        }

        return empty($this->getMessages());
    }
}
