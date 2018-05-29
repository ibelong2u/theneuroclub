<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Converter\QuoteConverter;
use Aheadworks\Sarp\Model\SubscriptionsCart\Converter\TaxQuoteDetailsConverter;
use Aheadworks\Sarp\Model\SubscriptionsCart\ConverterManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\ConverterManager
 */
class ConverterManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConverterManager
     */
    private $converterManager;

    /**
     * @var QuoteConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $toQuoteConverterMock;

    /**
     * @var TaxQuoteDetailsConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $toTaxQuoteDetailsConverterMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->toQuoteConverterMock = $this->createMock(QuoteConverter::class);
        $this->toTaxQuoteDetailsConverterMock = $this->createMock(TaxQuoteDetailsConverter::class);
        $this->converterManager = $objectManager->getObject(
            ConverterManager::class,
            [
                'toQuoteConverter' => $this->toQuoteConverterMock,
                'toTaxQuoteDetailsConverter' => $this->toTaxQuoteDetailsConverterMock
            ]
        );
    }

    public function testToQuote()
    {
        /** @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject $cartMock */
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $quoteMock = $this->createMock(Quote::class);
        $this->toQuoteConverterMock->expects($this->once())
            ->method('convert')
            ->with($cartMock)
            ->willReturn($quoteMock);
        $this->assertEquals($quoteMock, $this->converterManager->toQuote($cartMock));
    }

    public function testToTaxQuoteDetails()
    {
        /** @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject $cartMock */
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        /** @var QuoteDetailsItemInterface|\PHPUnit_Framework_MockObject_MockObject $taxQuoteDetailsItemMock */
        $taxQuoteDetailsItemMock = $this->getMockForAbstractClass(QuoteDetailsItemInterface::class);
        $taxQuoteDetailsMock = $this->getMockForAbstractClass(QuoteDetailsInterface::class);
        $this->toTaxQuoteDetailsConverterMock->expects($this->once())
            ->method('convert')
            ->with($cartMock, [$taxQuoteDetailsItemMock])
            ->willReturn($taxQuoteDetailsMock);
        $this->assertEquals(
            $taxQuoteDetailsMock,
            $this->converterManager->toTaxQuoteDetails($cartMock, [$taxQuoteDetailsItemMock])
        );
    }
}
