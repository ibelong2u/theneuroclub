<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterfaceFactory;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\CollectorInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\CollectorsList;
use Aheadworks\Sarp\Model\SubscriptionsCart\TotalsCollector;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\TotalsCollector
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TotalsCollectorTest extends \PHPUnit\Framework\TestCase
{
    const SHIPPING_AMOUNT = 5.00;
    const BASE_SHIPPING_AMOUNT = 10.00;
    const SUBTOTAL = 15.00;
    const BASE_SUBTOTAL = 30.00;
    const TRIAL_SUBTOTAL = 12.00;
    const BASE_TRIAL_SUBTOTAL = 24.00;
    const INITIAL_FEE = 0.00;
    const BASE_INITIAL_FEE = 0.00;
    const TAX_AMOUNT = 3.00;
    const BASE_TAX_AMOUNT = 6.00;
    const TRIAL_TAX_AMOUNT = 2.40;
    const BASE_TRIAL_TAX_AMOUNT = 4.80;
    const GRAND_TOTAL = 23.00;
    const BASE_GRAND_TOTAL = 46.00;

    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @var SubscriptionsCartTotalsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsFactoryMock;

    /**
     * @var CollectorsList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectorsListMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectProcessorMock;

    /**
     * @var array
     */
    private $calculatedAddressTotalsData = [
        'getShippingAmount' => self::SHIPPING_AMOUNT,
        'getBaseShippingAmount' => self::BASE_SHIPPING_AMOUNT,
        'getSubtotal' => self::SUBTOTAL,
        'getBaseSubtotal' => self::BASE_SUBTOTAL,
        'getTrialSubtotal' => self::TRIAL_SUBTOTAL,
        'getBaseTrialSubtotal' => self::BASE_TRIAL_SUBTOTAL,
        'getInitialFee' => self::INITIAL_FEE,
        'getBaseInitialFee' => self::BASE_INITIAL_FEE,
        'getTaxAmount' => self::TAX_AMOUNT,
        'getBaseTaxAmount' => self::BASE_TAX_AMOUNT,
        'getTrialTaxAmount' => self::TRIAL_TAX_AMOUNT,
        'getBaseTrialTaxAmount' => self::BASE_TRIAL_TAX_AMOUNT,
        'getGrandTotal' => self::GRAND_TOTAL,
        'getBaseGrandTotal' => self::BASE_GRAND_TOTAL
    ];

    /**
     * @var array
     */
    private $nonCalculatedAddressDataTotals = [
        'getShippingAmount' => 0,
        'getBaseShippingAmount' => 0,
        'getSubtotal' => 0,
        'getBaseSubtotal' => 0,
        'getTrialSubtotal' => 0,
        'getBaseTrialSubtotal' => 0,
        'getInitialFee' => 0,
        'getBaseInitialFee' => 0,
        'getTaxAmount' => 0,
        'getBaseTaxAmount' => 0,
        'getTrialTaxAmount' => 0,
        'getBaseTrialTaxAmount' => 0,
        'getGrandTotal' => 0,
        'getBaseGrandTotal' => 0
    ];

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->totalsFactoryMock = $this->createMock(SubscriptionsCartTotalsInterfaceFactory::class);
        $this->collectorsListMock = $this->createMock(CollectorsList::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->objectProcessorMock = $this->createMock(DataObjectProcessor::class);
        $this->totalsCollector = $objectManager->getObject(
            TotalsCollector::class,
            [
                'totalsFactory' => $this->totalsFactoryMock,
                'collectorsList' => $this->collectorsListMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'objectProcessor' => $this->objectProcessorMock
            ]
        );
    }

    public function testCollect()
    {
        /** @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject $cartMock */
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $totalsMock = $this->getMockForAbstractClass(SubscriptionsCartTotalsInterface::class);
        $shippingAddressTotalsMock = $this->getMockForAbstractClass(SubscriptionsCartTotalsInterface::class);
        $billingAddressTotalsMock = $this->getMockForAbstractClass(SubscriptionsCartTotalsInterface::class);
        $shippingAddressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $billingAddressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $collectorMock = $this->getMockForAbstractClass(CollectorInterface::class);

        $this->totalsFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                $totalsMock,
                $billingAddressTotalsMock,
                $shippingAddressTotalsMock
            );
        $cartMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$billingAddressMock, $shippingAddressMock]);
        $this->collectorsListMock->expects($this->exactly(2))
            ->method('getCollectors')
            ->willReturn([$collectorMock]);
        $collectorMock->expects($this->exactly(2))
            ->method('collect')
            ->withConsecutive(
                [$cartMock, $billingAddressMock, $billingAddressTotalsMock],
                [$cartMock, $shippingAddressMock, $shippingAddressTotalsMock]
            );
        $this->setUpAddressTotals($billingAddressTotalsMock, $this->nonCalculatedAddressDataTotals);
        $this->setUpAddressTotals($shippingAddressTotalsMock, $this->calculatedAddressTotalsData);

        $this->setUpMockGetterExpectation($totalsMock, 'getShippingAmount', null, 0);
        $this->setUpMockGetterExpectation($totalsMock, 'getBaseShippingAmount', null, 0);
        $this->setUpMockGetterExpectation($totalsMock, 'getSubtotal', null, 0);
        $this->setUpMockGetterExpectation($totalsMock, 'getBaseSubtotal', null, 0);
        $this->setUpMockGetterExpectation($totalsMock, 'getTrialSubtotal', null, 0);
        $this->setUpMockGetterExpectation($totalsMock, 'getBaseTrialSubtotal', null, 0);
        $this->setUpMockGetterExpectation($totalsMock, 'getInitialFee', null, 0);
        $this->setUpMockGetterExpectation($totalsMock, 'getBaseInitialFee', null, 0);
        $this->setUpMockGetterExpectation($totalsMock, 'getTaxAmount', null, 0);
        $this->setUpMockGetterExpectation($totalsMock, 'getBaseTaxAmount', null, 0);
        $this->setUpMockGetterExpectation($totalsMock, 'getTrialTaxAmount', null, 0);
        $this->setUpMockGetterExpectation($totalsMock, 'getBaseTrialTaxAmount', null, 0);
        $this->setUpMockGetterExpectation($totalsMock, 'getGrandTotal', null, 0);
        $this->setUpMockGetterExpectation($totalsMock, 'getBaseGrandTotal', null, 0);

        $this->setUpMockSetterExpectation($totalsMock, 'setShippingAmount', 0, self::SHIPPING_AMOUNT);
        $this->setUpMockSetterExpectation($totalsMock, 'setBaseShippingAmount', 0, self::BASE_SHIPPING_AMOUNT);
        $this->setUpMockSetterExpectation($totalsMock, 'setSubtotal', 0, self::SUBTOTAL);
        $this->setUpMockSetterExpectation($totalsMock, 'setBaseSubtotal', 0, self::BASE_SUBTOTAL);
        $this->setUpMockSetterExpectation($totalsMock, 'setTrialSubtotal', 0, self::TRIAL_SUBTOTAL);
        $this->setUpMockSetterExpectation($totalsMock, 'setBaseTrialSubtotal', 0, self::BASE_TRIAL_SUBTOTAL);
        $this->setUpMockSetterExpectation($totalsMock, 'setInitialFee', 0, self::INITIAL_FEE);
        $this->setUpMockSetterExpectation($totalsMock, 'setBaseInitialFee', 0, self::BASE_INITIAL_FEE);
        $this->setUpMockSetterExpectation($totalsMock, 'setTaxAmount', 0, self::TAX_AMOUNT);
        $this->setUpMockSetterExpectation($totalsMock, 'setBaseTaxAmount', 0, self::BASE_TAX_AMOUNT);
        $this->setUpMockSetterExpectation($totalsMock, 'setTrialTaxAmount', 0, self::TRIAL_TAX_AMOUNT);
        $this->setUpMockSetterExpectation($totalsMock, 'setBaseTrialTaxAmount', 0, self::BASE_TRIAL_TAX_AMOUNT);
        $this->setUpMockSetterExpectation($totalsMock, 'setGrandTotal', 0, self::GRAND_TOTAL);
        $this->setUpMockSetterExpectation($totalsMock, 'setBaseGrandTotal', 0, self::BASE_GRAND_TOTAL);

        $this->objectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($totalsMock, SubscriptionsCartTotalsInterface::class)
            ->willReturn($this->calculatedAddressTotalsData);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with(
                $cartMock,
                $this->calculatedAddressTotalsData,
                SubscriptionsCartInterface::class
            );

        $this->totalsCollector->collect($cartMock);
    }

    public function testCollectAddressTotals()
    {
        /** @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject $cartMock */
        $cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        /** @var SubscriptionsCartAddressInterface|\PHPUnit_Framework_MockObject_MockObject $addressMock */
        $addressMock = $this->getMockForAbstractClass(SubscriptionsCartAddressInterface::class);
        $totalsMock = $this->getMockForAbstractClass(SubscriptionsCartTotalsInterface::class);
        $collectorMock = $this->getMockForAbstractClass(CollectorInterface::class);

        $this->totalsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($totalsMock);
        $this->collectorsListMock->expects($this->once())
            ->method('getCollectors')
            ->willReturn([$collectorMock]);
        $collectorMock->expects($this->once())
            ->method('collect')
            ->with($cartMock, $addressMock, $totalsMock);

        $this->assertEquals(
            $totalsMock,
            $this->totalsCollector->collectAddressTotals($cartMock, $addressMock)
        );
    }

    /**
     * Set up address totals mock
     *
     * @param SubscriptionsCartTotalsInterface|\PHPUnit_Framework_MockObject_MockObject $addressTotalsMock
     * @param array $data
     * @return void
     */
    private function setUpAddressTotals($addressTotalsMock, $data = [])
    {
        foreach ($data as $method => $value) {
            $addressTotalsMock->expects($this->once())
                ->method($method)
                ->willReturn($value);
        }
    }

    /**
     * Set up mock's getter expectation
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param string $methodName
     * @param mixed $firstValue
     * @param mixed $secondValue
     * @return void
     */
    private function setUpMockGetterExpectation($mock, $methodName, $firstValue, $secondValue)
    {
        $mock->expects($this->exactly(2))
            ->method($methodName)
            ->willReturnOnConsecutiveCalls($firstValue, $secondValue);
    }

    /**
     * Set up mock's setter expectation
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param string $methodName
     * @param mixed $firstValue
     * @param mixed $secondValue
     * @return void
     */
    private function setUpMockSetterExpectation($mock, $methodName, $firstValue, $secondValue)
    {
        $mock->expects($this->exactly(2))
            ->method($methodName)
            ->willReturnOnConsecutiveCalls([$firstValue], [$secondValue])
            ->willReturnSelf();
    }
}
