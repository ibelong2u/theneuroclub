<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\CollectorInterface;

/**
 * Class Grand
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors
 */
class Grand implements CollectorInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(
        SubscriptionsCartInterface $cart,
        SubscriptionsCartAddressInterface $address,
        SubscriptionsCartTotalsInterface $totals
    ) {
        $totals
            ->setGrandTotal(0)
            ->setBaseGrandTotal(0);

        $grandTotal = $this->invokeSumGetters($totals, false);
        $baseGrandTotal = $this->invokeSumGetters($totals, true);

        $totals
            ->setGrandTotal($grandTotal)
            ->setBaseGrandTotal($baseGrandTotal);
    }

    /**
     * Invoke totals getters and sum results
     *
     * @param SubscriptionsCartTotalsInterface $totals
     * @param bool $isUseBaseCurrency
     * @return float
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function invokeSumGetters($totals, $isUseBaseCurrency)
    {
        $amount = 0.0;
        $methods = get_class_methods(SubscriptionsCartTotalsInterface::class);
        foreach ($methods as $method) {
            $possibleGetBasePrefix = substr($method, 0, 7);
            if (substr($method, 0, 3) == 'get'
                && ($isUseBaseCurrency && $possibleGetBasePrefix == 'getBase'
                    || !$isUseBaseCurrency && $possibleGetBasePrefix != 'getBase')
                && $method != 'getExtensionAttributes'
                && strpos($method, 'Trial') === false
                && strpos($method, 'Initial') === false
            ) {
                $amount += $totals->$method();
            }
        }
        return $amount;
    }
}
