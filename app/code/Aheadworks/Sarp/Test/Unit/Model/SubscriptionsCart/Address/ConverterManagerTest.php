<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart\Address;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Converter\CustomerAddressConverter;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Converter\QuoteAddressConverter;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ConverterManager;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Address\ConverterManager
 */
class ConverterManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConverterManager
     */
    private $converterManager;

    /**
     * @var QuoteAddressConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $toQuoteAddressConverterMock;

    /**
     * @var CustomerAddressConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAddressConverterMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->toQuoteAddressConverterMock = $this->createMock(QuoteAddressConverter::class);
        $this->customerAddressConverterMock = $this->createMock(CustomerAddressConverter::class);
        $this->converterManager = $objectManager->getObject(
            ConverterManager::class,
            [
                'toQuoteAddressConverter' => $this->toQuoteAddressConverterMock,
                'customerAddressConverter' => $this->customerAddressConverterMock
            ]
        );
    }

    public function testToQuoteAddress()
    {
        /** @var SubscriptionsCartAddressInterface|\PHPUnit_Framework_MockObject_MockObject $addressMock */
        $addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $quoteAddressMock = $this->createMock(QuoteAddress::class);
        $this->toQuoteAddressConverterMock->expects($this->once())
            ->method('convert')
            ->with($addressMock)
            ->willReturn($quoteAddressMock);
        $this->assertEquals($quoteAddressMock, $this->converterManager->toQuoteAddress($addressMock));
    }

    public function testToCustomerAddress()
    {
        /** @var SubscriptionsCartAddressInterface|\PHPUnit_Framework_MockObject_MockObject $addressMock */
        $addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $customerAddressMock = $this->getMockForAbstractClass(CustomerAddress::class);
        $this->customerAddressConverterMock->expects($this->once())
            ->method('toCustomerAddress')
            ->with($addressMock)
            ->willReturn($customerAddressMock);
        $this->assertEquals($customerAddressMock, $this->converterManager->toCustomerAddress($addressMock));
    }

    public function testFromCustomerAddress()
    {
        /** @var CustomerAddress|\PHPUnit_Framework_MockObject_MockObject $customerAddressMock */
        $customerAddressMock = $this->getMockForAbstractClass(CustomerAddress::class);
        $addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $this->customerAddressConverterMock->expects($this->once())
            ->method('fromCustomerAddress')
            ->with($customerAddressMock)
            ->willReturn($addressMock);
        $this->assertEquals($addressMock, $this->converterManager->fromCustomerAddress($customerAddressMock));
    }
}
