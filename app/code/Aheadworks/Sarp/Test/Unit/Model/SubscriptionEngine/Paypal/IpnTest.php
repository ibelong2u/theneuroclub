<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Paypal;

use Aheadworks\Sarp\Api\ProfileManagementInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterfaceFactory;
use Aheadworks\Sarp\Model\Profile\PaymentInfo;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\ProfileStatusFromApi as ProfileStatusFilter;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Ipn;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Ipn\Debugger;
use Magento\Directory\Model\Currency;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Ipn
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IpnTest extends \PHPUnit\Framework\TestCase
{
    const MC_GROSS = 100.00;
    const TAX = 5.00;
    const SHIPPING = 10.00;
    const TXN_ID = 'txn_id_value';
    const CURRENCY_CODE = 'EUR';

    /**
     * @var array
     */
    private $requestData = ['fieldName' => 'fieldValue'];

    /**
     * @var Ipn
     */
    private $ipn;

    /**
     * @var DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ProfileRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileRepositoryMock;

    /**
     * @var ProfileManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileManagementMock;

    /**
     * @var ProfileStatusFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileStatusFilterMock;

    /**
     * @var ProfilePaymentInfoInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentInfoFactoryMock;

    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var Debugger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $debuggerMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->requestMock = $this->createMock(DataObject::class);
        $this->profileRepositoryMock = $this->getMockForAbstractClass(ProfileRepositoryInterface::class);
        $this->profileManagementMock = $this->getMockForAbstractClass(ProfileManagementInterface::class);
        $this->profileStatusFilterMock = $this->createMock(ProfileStatusFilter::class);
        $this->paymentInfoFactoryMock = $this->createMock(ProfilePaymentInfoInterfaceFactory::class);
        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->debuggerMock = $this->createMock(Debugger::class);
        $dataObjectFactoryMock = $this->createMock(DataObjectFactory::class);
        $dataObjectFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->requestData)
            ->willReturn($this->requestMock);
        $this->ipn = $objectManager->getObject(
            Ipn::class,
            [
                'profileRepository' => $this->profileRepositoryMock,
                'profileManagement' => $this->profileManagementMock,
                'profileStatusFilter' => $this->profileStatusFilterMock,
                'debugger' => $this->debuggerMock,
                'paymentInfoFactory' => $this->paymentInfoFactoryMock,
                'priceCurrency' => $this->priceCurrencyMock,
                'dataObjectFactory' => $dataObjectFactoryMock,
                'data' => $this->requestData
            ]
        );
    }

    /**
     * Set up mocks for getPaymentInfo() method
     *
     * @param ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock
     * @param ProfilePaymentInfoInterface|\PHPUnit_Framework_MockObject_MockObject $paymentInfoMock
     * @return void
     */
    private function setUpGetPaymentInfo($profileMock, $paymentInfoMock)
    {
        $storeId = 1;
        $baseCurrencyCode = 'USD';
        $amount = self::MC_GROSS - self::TAX - self::SHIPPING;

        $currencyMock = $this->createMock(Currency::class);

        $profileMock->expects($this->once())
            ->method('getBaseCurrencyCode')
            ->willReturn($baseCurrencyCode);
        $profileMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->priceCurrencyMock->expects($this->once())
            ->method('getCurrency')
            ->with($storeId, self::CURRENCY_CODE)
            ->willReturn($currencyMock);
        $this->paymentInfoFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($paymentInfoMock);

        $currencyMock->expects($this->exactly(4))
            ->method('convert')
            ->withConsecutive(
                [$amount, $baseCurrencyCode],
                [self::TAX, $baseCurrencyCode],
                [self::SHIPPING, $baseCurrencyCode],
                [self::MC_GROSS, $baseCurrencyCode]
            )
            ->willReturnArgument(0);
        $paymentInfoMock->expects($this->once())
            ->method('setPaymentType')
            ->with(PaymentInfo::PAYMENT_TYPE_REGULAR)
            ->willReturnSelf();
        $paymentInfoMock->expects($this->once())
            ->method('setTransactionId')
            ->with(self::TXN_ID)
            ->willReturnSelf();
        $paymentInfoMock->expects($this->once())
            ->method('setBaseAmount')
            ->with($amount)
            ->willReturnSelf();
        $paymentInfoMock->expects($this->once())
            ->method('setAmount')
            ->with($amount)
            ->willReturnSelf();
        $paymentInfoMock->expects($this->once())
            ->method('setBaseTaxAmount')
            ->with(self::TAX)
            ->willReturnSelf();
        $paymentInfoMock->expects($this->once())
            ->method('setTaxAmount')
            ->with(self::TAX)
            ->willReturnSelf();
        $paymentInfoMock->expects($this->once())
            ->method('setBaseShippingAmount')
            ->with(self::SHIPPING)
            ->willReturnSelf();
        $paymentInfoMock->expects($this->once())
            ->method('setShippingAmount')
            ->with(self::SHIPPING)
            ->willReturnSelf();
        $paymentInfoMock->expects($this->once())
            ->method('setBaseGrandTotal')
            ->with(self::MC_GROSS)
            ->willReturnSelf();
        $paymentInfoMock->expects($this->once())
            ->method('setGrandTotal')
            ->with(self::MC_GROSS)
            ->willReturnSelf();
        $paymentInfoMock->expects($this->once())
            ->method('setBaseCurrencyCode')
            ->with($baseCurrencyCode)
            ->willReturnSelf();
        $paymentInfoMock->expects($this->once())
            ->method('setCurrencyCode')
            ->with(self::CURRENCY_CODE)
            ->willReturnSelf();
    }

    public function testProcessIpnRequestRecurringPayment()
    {
        $transactionType = Ipn::TXN_TYPE_RECURRING_PAYMENT;
        $recurringPaymentId = 'recurring_payment_id_value';

        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        $paymentInfoMock = $this->getMockForAbstractClass(ProfilePaymentInfoInterface::class);

        $this->requestMock->expects($this->once())
            ->method('getData')
            ->willReturn($this->requestData);
        $this->debuggerMock->expects($this->once())
            ->method('addDebugData')
            ->with('ipn', $this->requestData);
        $this->requestMock->expects($this->exactly(9))
            ->method('__call')
            ->withConsecutive(
                ['getTxnType'],
                ['getRecurringPaymentId'],
                ['getPaymentStatus'],
                ['getCurrencyCode'],
                ['getMcGross'],
                ['getTax'],
                ['getShipping'],
                ['getPeriodType'],
                ['getTxnId']
            )
            ->willReturnOnConsecutiveCalls(
                $transactionType,
                $recurringPaymentId,
                Ipn::PAYMENT_STATUS_COMPLETED,
                self::CURRENCY_CODE,
                self::MC_GROSS,
                self::TAX,
                self::SHIPPING,
                'Regular',
                self::TXN_ID
            );
        $this->profileRepositoryMock->expects($this->once())
            ->method('getByReferenceId')
            ->with($recurringPaymentId)
            ->willReturn($profileMock);
        $this->setUpGetPaymentInfo($profileMock, $paymentInfoMock);
        $this->profileManagementMock->expects($this->once())
            ->method('createOrder')
            ->with($profileMock, $paymentInfoMock);
        $this->debuggerMock->expects($this->once())
            ->method('debug');

        $this->ipn->processIpnRequest();
    }

    public function testProcessIpnRequestChangeStatus()
    {
        $transactionType = Ipn::TXN_TYPE_RECURRING_PAYMENT_SUSPENDED;
        $recurringPaymentId = 'recurring_payment_id_value';
        $profileStatus = 'suspended';

        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);

        $this->requestMock->expects($this->once())
            ->method('getData')
            ->willReturn($this->requestData);
        $this->debuggerMock->expects($this->once())
            ->method('addDebugData')
            ->with('ipn', $this->requestData);

        $this->requestMock->expects($this->exactly(3))
            ->method('__call')
            ->withConsecutive(
                ['getTxnType'],
                ['getRecurringPaymentId'],
                ['getProfileStatus']
            )
            ->willReturnOnConsecutiveCalls(
                $transactionType,
                $recurringPaymentId,
                $profileStatus
            );
        $this->profileRepositoryMock->expects($this->once())
            ->method('getByReferenceId')
            ->with($recurringPaymentId)
            ->willReturn($profileMock);
        $this->profileStatusFilterMock->expects($this->once())
            ->method('filter')
            ->with($profileStatus)
            ->willReturnArgument(0);
        $profileMock->expects($this->once())
            ->method('setStatus')
            ->with($profileStatus);
        $this->profileRepositoryMock->expects($this->once())
            ->method('save')
            ->with($profileMock);
        $this->debuggerMock->expects($this->once())
            ->method('debug');

        $this->ipn->processIpnRequest();
    }
}
