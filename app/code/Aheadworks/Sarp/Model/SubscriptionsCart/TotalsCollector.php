<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterfaceFactory;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\CollectorsList;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class TotalsCollector
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 */
class TotalsCollector
{
    /**
     * @var SubscriptionsCartTotalsInterfaceFactory
     */
    private $totalsFactory;

    /**
     * @var CollectorsList
     */
    private $collectorsList;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $objectProcessor;

    /**
     * @param SubscriptionsCartTotalsInterfaceFactory $totalsFactory
     * @param CollectorsList $collectorsList
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $objectProcessor
     */
    public function __construct(
        SubscriptionsCartTotalsInterfaceFactory $totalsFactory,
        CollectorsList $collectorsList,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $objectProcessor
    ) {
        $this->totalsFactory = $totalsFactory;
        $this->collectorsList = $collectorsList;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->objectProcessor = $objectProcessor;
    }

    /**
     * Collect totals
     *
     * @param SubscriptionsCartInterface $cart
     * @return void
     */
    public function collect(SubscriptionsCartInterface $cart)
    {
        /** @var SubscriptionsCartTotalsInterface $totals */
        $totals = $this->totalsFactory->create();

        foreach ($cart->getAddresses() as $address) {
            $addressTotals = $this->collectAddressTotals($cart, $address);

            $totals
                ->setShippingAmount(
                    (float)$totals->getShippingAmount() + $addressTotals->getShippingAmount()
                )
                ->setBaseShippingAmount(
                    (float)$totals->getBaseShippingAmount() + $addressTotals->getBaseShippingAmount()
                )
                ->setSubtotal(
                    (float)$totals->getSubtotal() + $addressTotals->getSubtotal()
                )
                ->setBaseSubtotal(
                    (float)$totals->getBaseSubtotal() + $addressTotals->getBaseSubtotal()
                )
                ->setTrialSubtotal(
                    (float)$totals->getTrialSubtotal() + $addressTotals->getTrialSubtotal()
                )
                ->setBaseTrialSubtotal(
                    (float)$totals->getBaseTrialSubtotal() + $addressTotals->getBaseTrialSubtotal()
                )
                ->setInitialFee(
                    (float)$totals->getInitialFee() + $addressTotals->getInitialFee()
                )
                ->setBaseInitialFee(
                    (float)$totals->getBaseInitialFee() + $addressTotals->getBaseInitialFee()
                )
                ->setTaxAmount(
                    (float)$totals->getTaxAmount() + $addressTotals->getTaxAmount()
                )
                ->setBaseTaxAmount(
                    (float)$totals->getBaseTaxAmount() + $addressTotals->getBaseTaxAmount()
                )
                ->setTrialTaxAmount(
                    (float)$totals->getTrialTaxAmount() + $addressTotals->getTrialTaxAmount()
                )
                ->setBaseTrialTaxAmount(
                    (float)$totals->getBaseTrialTaxAmount() + $addressTotals->getBaseTrialTaxAmount()
                )
                ->setGrandTotal(
                    (float)$totals->getGrandTotal() + $addressTotals->getGrandTotal()
                )
                ->setBaseGrandTotal(
                    (float)$totals->getBaseGrandTotal() + $addressTotals->getBaseGrandTotal()
                );
        }

        $this->dataObjectHelper->populateWithArray(
            $cart,
            $this->objectProcessor->buildOutputDataArray($totals, SubscriptionsCartTotalsInterface::class),
            SubscriptionsCartInterface::class
        );
    }

    /**
     * Collect address totals
     *
     * @param SubscriptionsCartInterface $cart
     * @param SubscriptionsCartAddressInterface $address
     * @return SubscriptionsCartTotalsInterface
     */
    public function collectAddressTotals(
        SubscriptionsCartInterface $cart,
        SubscriptionsCartAddressInterface $address
    ) {
        /** @var SubscriptionsCartTotalsInterface $totals */
        $totals = $this->totalsFactory->create();

        foreach ($this->collectorsList->getCollectors() as $collector) {
            $collector->collect($cart, $address, $totals);
        }

        return $totals;
    }
}
