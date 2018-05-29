<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class SubscriptionsCartPlugin
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 */
class SubscriptionsCartPlugin
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
     * Subscription cart start date adjustment
     *
     * @param SubscriptionsCartRepositoryInterface $subject
     * @param \Closure $proceed
     * @param array ...$args
     * @return SubscriptionsCartInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // @codingStandardsIgnoreStart
    public function aroundSave(SubscriptionsCartRepositoryInterface $subject, \Closure $proceed, ...$args)
    {
        if (isset($args[0])) {
            /** @var SubscriptionsCartInterface $cart */
            $cart = $args[0];
            if ($cart->getStartDate()) {
                $currentDate = $this->dateChecker->getCurrentDate();
                $startDate = new \Zend_Date($cart->getStartDate(), DateTime::DATE_INTERNAL_FORMAT, 'en_US');

                if ($startDate->isEarlier($currentDate)) {
                    $startDate->addDay(1);
                    $cart->setStartDate($startDate->toString(DateTime::DATE_INTERNAL_FORMAT));
                }
            }
        }
        $result = $proceed(...$args);
        return $result;
    }
    // @codingStandardsIgnoreEnd
}
