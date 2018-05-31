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
use Aheadworks\Sarp\Model\SubscriptionsCart\ConverterManager as CartConverterManager;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Tax\Shipping;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Totals\Collectors\Tax\Shipping
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Shipping
     */
    private $collector;

    /**
     * @var ItemsRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressItemsRegistryMock;

    /**
     * @var CartConverterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartConverterManagerMock;

    /**
     * @var QuoteDetailsItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxQuoteDetailsItemFactoryMock;

    /**
     * @var TaxClassKeyInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxClassKeyFactoryMock;

    /**
     * @var TaxCalculationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxCalculationMock;

    /**
     * @var TaxConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxConfigMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

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
        $this->cartConverterManagerMock = $this->createMock(CartConverterManager::class);
        $this->taxQuoteDetailsItemFactoryMock = $this->createMock(QuoteDetailsItemInterfaceFactory::class);
        $this->taxClassKeyFactoryMock = $this->createMock(TaxClassKeyInterfaceFactory::class);
        $this->taxCalculationMock = $this->getMockForAbstractClass(TaxCalculationInterface::class);
        $this->taxConfigMock = $this->createMock(TaxConfig::class);
        $this->configMock = $this->createMock(Config::class);

        $this->cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $this->addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $this->totalsMock = $this->getMockForAbstractClass(SubscriptionsCartTotalsInterface::class);

        $this->collector = $objectManager->getObject(
            Shipping::class,
            [
                'addressItemsRegistry' => $this->addressItemsRegistryMock,
                'cartConverterManager' => $this->cartConverterManagerMock,
                'taxQuoteDetailsItemFactory' => $this->taxQuoteDetailsItemFactoryMock,
                'taxClassKeyFactory' => $this->taxClassKeyFactoryMock,
                'taxCalculation' => $this->taxCalculationMock,
                'taxConfig' => $this->taxConfigMock,
                'config' => $this->configMock
            ]
        );
    }

    /**
     * @param $isApplyTaxOnShippingAmount
     * @dataProvider collectDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCollect($isApplyTaxOnShippingAmount)
    {
        $shippingAmount = 5.00;
        $baseShippingAmount = 7.50;
        $taxAmount = 2.00;
        $baseTaxAmount = 3.00;
        $shippingTaxAmount = 1.00;
        $baseShippingTaxAmount = 1.50;
        $shippingTaxClass = 2;
        $isShippingPriceIncludesTax = false;

        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        $quoteDetailsMock = $this->getMockForAbstractClass(QuoteDetailsInterface::class);
        $quoteItemDetailsMock = $this->getMockForAbstractClass(QuoteDetailsItemInterface::class);
        $baseQuoteDetailsMock = $this->getMockForAbstractClass(QuoteDetailsInterface::class);
        $baseQuoteItemDetailsMock = $this->getMockForAbstractClass(QuoteDetailsItemInterface::class);
        $taxClassKeyMock = $this->getMockForAbstractClass(TaxClassKeyInterface::class);
        $baseTaxClassKeyMock = $this->getMockForAbstractClass(TaxClassKeyInterface::class);
        $taxDetailsMock = $this->getMockForAbstractClass(TaxDetailsInterface::class);
        $shippingTaxDetailsMock = $this->getMockForAbstractClass(TaxDetailsItemInterface::class);
        $baseTaxDetailsMock = $this->getMockForAbstractClass(TaxDetailsInterface::class);
        $baseShippingTaxDetailsMock = $this->getMockForAbstractClass(TaxDetailsItemInterface::class);

        $this->addressItemsRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($this->addressMock, $this->cartMock)
            ->willReturn([$itemMock]);
        $this->taxQuoteDetailsItemFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($quoteItemDetailsMock, $baseQuoteItemDetailsMock);
        $this->taxClassKeyFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($taxClassKeyMock, $baseTaxClassKeyMock);
        $this->taxConfigMock->expects($this->exactly(2))
            ->method('getShippingTaxClass')
            ->willReturn($shippingTaxClass);
        $this->taxConfigMock->expects($this->exactly(2))
            ->method('shippingPriceIncludesTax')
            ->willReturn($isShippingPriceIncludesTax);

        $this->totalsMock->expects($this->once())
            ->method('getShippingAmount')
            ->willReturn($shippingAmount);
        $quoteItemDetailsMock->expects($this->once())
            ->method('setCode')
            ->with('shipping')
            ->willReturnSelf();
        $quoteItemDetailsMock->expects($this->once())
            ->method('setType')
            ->with('shipping')
            ->willReturnSelf();
        $quoteItemDetailsMock->expects($this->once())
            ->method('setQuantity')
            ->with(1)
            ->willReturnSelf();
        $quoteItemDetailsMock->expects($this->once())
            ->method('setUnitPrice')
            ->with($shippingAmount)
            ->willReturnSelf();
        $taxClassKeyMock->expects($this->once())
            ->method('setType')
            ->with(TaxClassKeyInterface::TYPE_ID)
            ->willReturnSelf();
        $taxClassKeyMock->expects($this->once())
            ->method('setValue')
            ->with($shippingTaxClass)
            ->willReturnSelf();
        $quoteItemDetailsMock->expects($this->once())
            ->method('setTaxClassKey')
            ->with($taxClassKeyMock)
            ->willReturnSelf();
        $quoteItemDetailsMock->expects($this->once())
            ->method('setIsTaxIncluded')
            ->with($isShippingPriceIncludesTax)
            ->willReturnSelf();

        $this->totalsMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn($baseShippingAmount);
        $baseQuoteItemDetailsMock->expects($this->once())
            ->method('setCode')
            ->with('shipping')
            ->willReturnSelf();
        $baseQuoteItemDetailsMock->expects($this->once())
            ->method('setType')
            ->with('shipping')
            ->willReturnSelf();
        $baseQuoteItemDetailsMock->expects($this->once())
            ->method('setQuantity')
            ->with(1)
            ->willReturnSelf();
        $baseQuoteItemDetailsMock->expects($this->once())
            ->method('setUnitPrice')
            ->with($baseShippingAmount)
            ->willReturnSelf();
        $baseTaxClassKeyMock->expects($this->once())
            ->method('setType')
            ->with(TaxClassKeyInterface::TYPE_ID)
            ->willReturnSelf();
        $baseTaxClassKeyMock->expects($this->once())
            ->method('setValue')
            ->with($shippingTaxClass)
            ->willReturnSelf();
        $baseQuoteItemDetailsMock->expects($this->once())
            ->method('setTaxClassKey')
            ->with($baseTaxClassKeyMock)
            ->willReturnSelf();
        $baseQuoteItemDetailsMock->expects($this->once())
            ->method('setIsTaxIncluded')
            ->with($isShippingPriceIncludesTax)
            ->willReturnSelf();

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
            ->method('getItems')
            ->willReturn(['shipping' => $shippingTaxDetailsMock]);
        $shippingTaxDetailsMock->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($shippingAmount);
        $this->totalsMock->expects($this->once())
            ->method('setShippingAmount')
            ->with($shippingAmount)
            ->willReturnSelf();
        $baseTaxDetailsMock->expects($this->once())
            ->method('getItems')
            ->willReturn(['shipping' => $baseShippingTaxDetailsMock]);
        $baseShippingTaxDetailsMock->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($baseShippingAmount);
        $this->totalsMock->expects($this->once())
            ->method('setBaseShippingAmount')
            ->with($baseShippingAmount)
            ->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('isApplyTaxOnShippingAmount')
            ->willReturn($isApplyTaxOnShippingAmount);
        if ($isApplyTaxOnShippingAmount) {
            $this->totalsMock->expects($this->once())
                ->method('getTaxAmount')
                ->willReturn($taxAmount);
            $shippingTaxDetailsMock->expects($this->once())
                ->method('getRowTax')
                ->willReturn($shippingTaxAmount);
            $this->totalsMock->expects($this->once())
                ->method('setTaxAmount')
                ->with($taxAmount + $shippingTaxAmount)
                ->willReturnSelf();
            $this->totalsMock->expects($this->once())
                ->method('getBaseTaxAmount')
                ->willReturn($baseTaxAmount);
            $baseShippingTaxDetailsMock->expects($this->once())
                ->method('getRowTax')
                ->willReturn($baseShippingTaxAmount);
            $this->totalsMock->expects($this->once())
                ->method('setBaseTaxAmount')
                ->with($baseTaxAmount + $baseShippingTaxAmount)
                ->willReturnSelf();
        }

        $this->collector->collect($this->cartMock, $this->addressMock, $this->totalsMock);
    }

    /**
     * @return array
     */
    public function collectDataProvider()
    {
        return [[true], [false]];
    }
}
