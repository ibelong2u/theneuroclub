<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart\Totals\Collectors;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Grand;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Grand
 */
class GrandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Grand
     */
    private $collector;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->collector = $objectManager->getObject(Grand::class);
    }

    /**
     * @param SubscriptionsCartTotalsInterface $totals
     * @param float $grandTotal
     * @param float $baseGrandTotal
     * @dataProvider collectDataProvider
     */
    public function testCollect($totals, $grandTotal, $baseGrandTotal)
    {
        /** @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject $cartMock */
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        /** @var SubscriptionsCartAddressInterface|\PHPUnit_Framework_MockObject_MockObject $addressMock */
        $addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $this->collector->collect($cartMock, $addressMock, $totals);
        $this->assertEquals($grandTotal, $totals->getGrandTotal());
        $this->assertEquals($baseGrandTotal, $totals->getBaseGrandTotal());
    }

    /**
     * Create totals instance
     *
     * @param array $data
     * @return SubscriptionsCartTotalsInterface
     */
    private function createTotals(array $data)
    {
        $objectManager = new ObjectManager($this);
        $totals = $objectManager->getObject(Totals::class);
        foreach ($data as $method => $value) {
            $totals->$method($value);
        }
        return $totals;
    }

    /**
     * @return array
     */
    public function collectDataProvider()
    {
        return [
            [
                $this->createTotals(
                    [
                        'setGrandTotal' => 0,
                        'setBaseGrandTotal' => 0,
                        'setSubtotal' => 50.00,
                        'setBaseSubtotal' => 75.00,
                        'setShippingAmount' => 5.00,
                        'setBaseShippingAmount' => 7.50,
                        'setTaxAmount' => 2.00,
                        'setBaseTaxAmount' => 3.00
                    ]
                ),
                57.00,
                85.50
            ],
            [
                $this->createTotals(
                    [
                        'setGrandTotal' => 10.00,
                        'setBaseGrandTotal' => 15.00,
                        'setSubtotal' => 50.00,
                        'setBaseSubtotal' => 75.00,
                        'setShippingAmount' => 5.00,
                        'setBaseShippingAmount' => 7.50,
                        'setTaxAmount' => 2.00,
                        'setBaseTaxAmount' => 3.00
                    ]
                ),
                57.00,
                85.50
            ],
            [
                $this->createTotals(
                    [
                        'setGrandTotal' => 0,
                        'setBaseGrandTotal' => 0,
                        'setSubtotal' => 50.00,
                        'setBaseSubtotal' => 75.00,
                        'setShippingAmount' => 5.00,
                        'setBaseShippingAmount' => 7.50,
                        'setTaxAmount' => 2.00,
                        'setBaseTaxAmount' => 3.00,
                        'setTrialSubtotal' => 40.00,
                        'setBaseTrialSubtotal' => 60.00,
                        'setTrialTaxAmount' => 1.00,
                        'setBaseTrialTaxAmount' => 0.50,
                        'setInitialFee' => 4.00,
                        'setBaseInitialFee' => 6.00
                    ]
                ),
                57.00,
                85.50
            ],
        ];
    }
}
