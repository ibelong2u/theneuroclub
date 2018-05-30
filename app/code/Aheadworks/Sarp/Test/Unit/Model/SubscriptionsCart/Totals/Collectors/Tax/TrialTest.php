<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart\Totals\Collectors\Tax;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;
use Aheadworks\Sarp\Model\Config;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ItemsRegistry;
use Aheadworks\Sarp\Model\SubscriptionsCart\Item\ConverterManager as ItemsConverterManager;
use Aheadworks\Sarp\Model\SubscriptionsCart\ConverterManager as CartConverterManager;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Tax\Trial;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Tax\Trial
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TrialTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Trial
     */
    private $collector;

    /**
     * @var ItemsRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressItemsRegistryMock;

    /**
     * @var ItemsConverterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemsConverterManagerMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var CartConverterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartConverterManagerMock;

    /**
     * @var TaxCalculationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxCalculationMock;

    /**
     * @var TaxConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxConfigMock;

    /**
     * @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartMock;

    /**
     * @var SubscriptionsCartAddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMock;

    /**
     * @var SubscriptionsCartTotalsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->addressItemsRegistryMock = $this->createMock(ItemsRegistry::class);
        $this->itemsConverterManagerMock = $this->createMock(ItemsConverterManager::class);
        $this->configMock = $this->createMock(Config::class);
        $this->cartConverterManagerMock = $this->createMock(CartConverterManager::class);
        $this->taxCalculationMock = $this->getMockForAbstractClass(TaxCalculationInterface::class);
        $this->taxConfigMock = $this->createMock(TaxConfig::class);

        $this->cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $this->addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $this->totalsMock = $this->getMockForAbstractClass(SubscriptionsCartTotalsInterface::class);

        $this->collector = $objectManager->getObject(
            Trial::class,
            [
                'addressItemsRegistry' => $this->addressItemsRegistryMock,
                'itemsConverterManager' => $this->itemsConverterManagerMock,
                'config' => $this->configMock,
                'cartConverterManager' => $this->cartConverterManagerMock,
                'taxCalculation' => $this->taxCalculationMock,
                'taxConfig' => $this->taxConfigMock
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCollect()
    {
        $trialTaxAmount = 3.00;
        $baseTrialTaxAmount = 4.50;
        $isPriceIncludesTax = false;
        $taxCalculationItemId = 1;

        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $quoteDetailsMock = $this->getMockForAbstractClass(QuoteDetailsInterface::class);
        $quoteItemDetailsMock = $this->getMockForAbstractClass(QuoteDetailsItemInterface::class);
        $baseQuoteDetailsMock = $this->getMockForAbstractClass(QuoteDetailsInterface::class);
        $baseQuoteItemDetailsMock = $this->getMockForAbstractClass(QuoteDetailsItemInterface::class);
        $taxDetailsMock = $this->getMockForAbstractClass(TaxDetailsInterface::class);
        $taxDetailsItemMock = $this->getMockForAbstractClass(TaxDetailsItemInterface::class);
        $baseTaxDetailsMock = $this->getMockForAbstractClass(TaxDetailsInterface::class);
        $baseTaxDetailsItemMock = $this->getMockForAbstractClass(TaxDetailsItemInterface::class);

        $this->addressItemsRegistryMock->expects($this->once())
            ->method('retrieveInner')
            ->with($this->addressMock, $this->cartMock)
            ->willReturn([$itemMock]);
        $this->configMock->expects($this->once())
            ->method('isApplyTaxOnTrialAmount')
            ->willReturn(true);
        $this->taxConfigMock->expects($this->once())
            ->method('priceIncludesTax')
            ->willReturn($isPriceIncludesTax);
        $this->itemsConverterManagerMock->expects($this->exactly(2))
            ->method('toTaxQuoteDetailsItems')
            ->willReturnMap(
                [
                    [[$itemMock], $isPriceIncludesTax, false, true, [$quoteItemDetailsMock]],
                    [[$itemMock], $isPriceIncludesTax, true, true, [$baseQuoteItemDetailsMock]]
                ]
            );
        $this->cartConverterManagerMock->expects($this->exactly(2))
            ->method('toTaxQuoteDetails')
            ->willReturnMap(
                [
                    [$this->cartMock, [$quoteItemDetailsMock], $quoteDetailsMock],
                    [$this->cartMock, [$baseQuoteItemDetailsMock], $baseQuoteDetailsMock]
                ]
            );
        $this->taxCalculationMock->expects($this->exactly(2))
            ->method('calculateTax')
            ->willReturnMap(
                [
                    [$quoteDetailsMock, null, true, $taxDetailsMock],
                    [$baseQuoteDetailsMock, null, true, $baseTaxDetailsMock]
                ]
            );
        $taxDetailsMock->expects($this->once())
            ->method('getTaxAmount')
            ->willReturn($trialTaxAmount);
        $taxDetailsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$taxCalculationItemId => $taxDetailsItemMock]);
        $itemMock->expects($this->exactly(4))
            ->method('getTaxCalculationItemId')
            ->willReturn($taxCalculationItemId);
        $taxDetailsItemMock->expects($this->once())
            ->method('getPrice')
            ->willReturn(30.00);
        $itemMock->expects($this->once())
            ->method('setTrialPrice')
            ->with(30.00)
            ->willReturnSelf();
        $taxDetailsItemMock->expects($this->once())
            ->method('getPriceInclTax')
            ->willReturn(31.50);
        $itemMock->expects($this->once())
            ->method('setTrialPriceInclTax')
            ->with(31.50)
            ->willReturnSelf();
        $taxDetailsItemMock->expects($this->once())
            ->method('getRowTotal')
            ->willReturn(60.00);
        $itemMock->expects($this->once())
            ->method('setTrialRowTotal')
            ->with(60.00)
            ->willReturnSelf();
        $taxDetailsItemMock->expects($this->once())
            ->method('getRowTotalInclTax')
            ->willReturn(63.00);
        $itemMock->expects($this->once())
            ->method('setTrialRowTotalInclTax')
            ->with(63.00)
            ->willReturnSelf();
        $taxDetailsItemMock->expects($this->once())
            ->method('getRowTax')
            ->willReturn(3.00);
        $itemMock->expects($this->once())
            ->method('setTrialTaxAmount')
            ->with(3.00)
            ->willReturnSelf();
        $taxDetailsItemMock->expects($this->once())
            ->method('getTaxPercent')
            ->willReturn(10.00);
        $itemMock->expects($this->exactly(2))
            ->method('setTaxPercent')
            ->with(10.00)
            ->willReturnSelf();
        $itemMock->expects($this->exactly(2))
            ->method('getIsDeleted')
            ->willReturn(false);
        $itemMock->expects($this->exactly(2))
            ->method('getParentItemId')
            ->willReturn(null);
        $itemMock->expects($this->once())
            ->method('getTrialRowTotal')
            ->willReturn(60.00);

        $baseTaxDetailsMock->expects($this->once())
            ->method('getTaxAmount')
            ->willReturn($baseTrialTaxAmount);
        $baseTaxDetailsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$taxCalculationItemId => $baseTaxDetailsItemMock]);
        $baseTaxDetailsItemMock->expects($this->once())
            ->method('getPrice')
            ->willReturn(45.00);
        $itemMock->expects($this->once())
            ->method('setBaseTrialPrice')
            ->with(45.00)
            ->willReturnSelf();
        $baseTaxDetailsItemMock->expects($this->once())
            ->method('getPriceInclTax')
            ->willReturn(47.25);
        $itemMock->expects($this->once())
            ->method('setBaseTrialPriceInclTax')
            ->with(47.25)
            ->willReturnSelf();
        $baseTaxDetailsItemMock->expects($this->once())
            ->method('getRowTotal')
            ->willReturn(90.00);
        $itemMock->expects($this->once())
            ->method('setBaseTrialRowTotal')
            ->with(90.00)
            ->willReturnSelf();
        $baseTaxDetailsItemMock->expects($this->once())
            ->method('getRowTotalInclTax')
            ->willReturn(94.50);
        $itemMock->expects($this->once())
            ->method('setBaseTrialRowTotalInclTax')
            ->with(94.50)
            ->willReturnSelf();
        $baseTaxDetailsItemMock->expects($this->once())
            ->method('getRowTax')
            ->willReturn(4.50);
        $itemMock->expects($this->once())
            ->method('setBaseTrialTaxAmount')
            ->with(4.50)
            ->willReturnSelf();
        $baseTaxDetailsItemMock->expects($this->once())
            ->method('getTaxPercent')
            ->willReturn(10.00);
        $itemMock->expects($this->once())
            ->method('getBaseTrialRowTotal')
            ->willReturn(90.00);

        $this->totalsMock->expects($this->once())
            ->method('setTrialSubtotal')
            ->with(60.00)
            ->willReturnSelf();
        $this->totalsMock->expects($this->once())
            ->method('setBaseTrialSubtotal')
            ->with(90.00)
            ->willReturnSelf();
        $this->totalsMock->expects($this->once())
            ->method('setTrialTaxAmount')
            ->with($trialTaxAmount)
            ->willReturnSelf();
        $this->totalsMock->expects($this->once())
            ->method('setBaseTrialTaxAmount')
            ->with($baseTrialTaxAmount)
            ->willReturnSelf();

        $this->collector->collect($this->cartMock, $this->addressMock, $this->totalsMock);
    }

    public function testCollectNotEnabled()
    {
        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);

        $this->addressItemsRegistryMock->expects($this->once())
            ->method('retrieveInner')
            ->with($this->addressMock, $this->cartMock)
            ->willReturn([$itemMock]);
        $this->configMock->expects($this->once())
            ->method('isApplyTaxOnTrialAmount')
            ->willReturn(false);
        $this->totalsMock->expects($this->never())
            ->method('setTrialSubtotal');
        $this->totalsMock->expects($this->never())
            ->method('setBaseTrialSubtotal');
        $this->totalsMock->expects($this->once())
            ->method('setTrialTaxAmount')
            ->with(0)
            ->willReturnSelf();
        $this->totalsMock->expects($this->once())
            ->method('setBaseTrialTaxAmount')
            ->with(0)
            ->willReturnSelf();

        $this->collector->collect($this->cartMock, $this->addressMock, $this->totalsMock);
    }

    /**
     * @param array $itemMocks
     * @param bool $isUseBaseCurrency
     * @param float $subtotal
     * @dataProvider calculateSubtotalDataProvider
     */
    public function testCalculateTrialSubtotal($itemMocks, $isUseBaseCurrency, $subtotal)
    {
        $class = new \ReflectionClass($this->collector);
        $method = $class->getMethod('calculateTrialSubtotal');
        $method->setAccessible(true);

        $this->assertEquals(
            $subtotal,
            $method->invokeArgs($this->collector, [$itemMocks, $isUseBaseCurrency])
        );
    }

    /**
     * Create item mock and setup getters expectations
     *
     * @param array $data
     * @return SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createItemMock(array $data)
    {
        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        foreach ($data as $method => $value) {
            $itemMock->expects($this->any())
                ->method($method)
                ->willReturn($value);
        }
        return $itemMock;
    }

    /**
     * @return array
     */
    public function calculateSubtotalDataProvider()
    {
        return [
            [
                [
                    $this->createItemMock(
                        [
                            'getIsDeleted' => false,
                            'getParentItemId' => null,
                            'getBaseTrialRowTotal' => 10.00,
                            'getTrialRowTotal' => 7.50
                        ]
                    )
                ],
                true,
                10.00
            ],
            [
                [
                    $this->createItemMock(
                        [
                            'getIsDeleted' => false,
                            'getParentItemId' => null,
                            'getBaseTrialRowTotal' => 10.00,
                            'getTrialRowTotal' => 7.50
                        ]
                    )
                ],
                false,
                7.50
            ],
            [
                [
                    $this->createItemMock(
                        [
                            'getIsDeleted' => true,
                            'getParentItemId' => null,
                            'getBaseTrialRowTotal' => 10.00,
                            'getTrialRowTotal' => 7.50
                        ]
                    )
                ],
                true,
                0
            ],
            [
                [
                    $this->createItemMock(
                        [
                            'getIsDeleted' => false,
                            'getParentItemId' => 1,
                            'getBaseTrialRowTotal' => 10.00,
                            'getTrialRowTotal' => 7.50
                        ]
                    )
                ],
                true,
                0
            ]
        ];
    }
}
