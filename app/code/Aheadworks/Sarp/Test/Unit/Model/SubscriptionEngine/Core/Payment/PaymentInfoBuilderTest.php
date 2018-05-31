<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Core\Payment;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterfaceFactory;
use Aheadworks\Sarp\Model\Profile\PaymentInfo;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\PaymentInfoBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\PaymentInfoBuilder
 */
class PaymentInfoBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentInfoBuilder
     */
    private $builder;

    /**
     * @var ProfilePaymentInfoInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factoryMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->factoryMock = $this->createMock(ProfilePaymentInfoInterfaceFactory::class);
        $this->builder = $objectManager->getObject(
            PaymentInfoBuilder::class,
            ['factory' => $this->factoryMock]
        );
    }

    public function testSetProfile()
    {
        /** @var ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);

        $this->builder->setProfile($profileMock);

        $class = new \ReflectionClass($this->builder);
        $dataProperty = $class->getProperty('data');
        $dataProperty->setAccessible(true);
        $value = $dataProperty->getValue($this->builder);

        $this->assertArrayHasKey('profile', $value);
        $this->assertSame($profileMock, $value['profile']);
    }

    public function testSetPaymentType()
    {
        $paymentType = 'regular';

        $this->builder->setPaymentType($paymentType);

        $class = new \ReflectionClass($this->builder);
        $dataProperty = $class->getProperty('data');
        $dataProperty->setAccessible(true);
        $value = $dataProperty->getValue($this->builder);

        $this->assertArrayHasKey('payment_type', $value);
        $this->assertEquals($paymentType, $value['payment_type']);
    }

    public function testResetState()
    {
        $class = new \ReflectionClass($this->builder);
        $dataProperty = $class->getProperty('data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($this->builder, ['field' => 'value']);

        $method = $class->getMethod('resetState');
        $method->setAccessible(true);
        $method->invoke($this->builder);

        $value = $dataProperty->getValue($this->builder);
        $this->assertTrue(is_array($value));
        $this->assertEmpty($dataProperty->getValue($this->builder));
    }

    /**
     * @param array $data
     * @param bool $result
     * @dataProvider isStateValidForBuildDataProvider
     */
    public function testIsStateValidForBuild($data, $result)
    {
        $class = new \ReflectionClass($this->builder);
        $dataProperty = $class->getProperty('data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($this->builder, $data);

        $method = $class->getMethod('isStateValidForBuild');
        $method->setAccessible(true);

        $this->assertEquals($result, $method->invoke($this->builder));
    }

    /**
     * @param string $paymentType
     * @param ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock
     * @param ProfilePaymentInfoInterface|\PHPUnit_Framework_MockObject_MockObject $paymentInfoMock
     * @dataProvider buildDataProvider
     */
    public function testBuild($paymentType, $profileMock, $paymentInfoMock)
    {
        $this->factoryMock->expects($this->once())
            ->method('create')
            ->willReturn($paymentInfoMock);

        $class = new \ReflectionClass($this->builder);
        $dataProperty = $class->getProperty('data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($this->builder, ['payment_type' => $paymentType, 'profile' => $profileMock]);

        $this->assertSame($paymentInfoMock, $this->builder->build());
    }

    /**
     * Create profile mock
     *
     * @param array $data
     * @return ProfileInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createProfileMock($data)
    {
        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        foreach ($data as $method => $value) {
            $profileMock->expects($this->any())
                ->method($method)
                ->willReturn($value);
        }
        return $profileMock;
    }

    /**
     * Create payment info mock
     *
     * @param array $data
     * @return ProfilePaymentInfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createPaymentInfoMock($data)
    {
        $paymentInfoMock = $this->getMockForAbstractClass(ProfilePaymentInfoInterface::class);
        foreach ($data as $method => $value) {
            $paymentInfoMock->expects($this->atLeastOnce())
                ->method($method)
                ->with($value)
                ->willReturnSelf();
        }
        return $paymentInfoMock;
    }

    /**
     * @return array
     */
    public function isStateValidForBuildDataProvider()
    {
        return [
            [[], false],
            [['profile' => $this->getMockForAbstractClass(ProfileInterface::class)], false],
            [['payment_type' => 'regular'], false],
            [
                [
                    'profile' => $this->getMockForAbstractClass(ProfileInterface::class),
                    'payment_type' => 'regular'
                ],
                true
            ]
        ];
    }

    /**
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            'initial payment' => [
                PaymentInfo::PAYMENT_TYPE_INITIAL,
                $this->createProfileMock(
                    [
                        'getProfileCurrencyCode' => 'EUR',
                        'getBaseCurrencyCode' => 'USD',
                        'getInitialFee' => 10.00,
                        'getBaseInitialFee' => 15.00
                    ]
                ),
                $this->createPaymentInfoMock(
                    [
                        'setPaymentType' => PaymentInfo::PAYMENT_TYPE_INITIAL,
                        'setCurrencyCode' => 'EUR',
                        'setBaseCurrencyCode' => 'USD',
                        'setAmount' => 10.00,
                        'setBaseAmount' => 15.00,
                        'setShippingAmount' => 0,
                        'setBaseShippingAmount' => 0,
                        'setTaxAmount' => 0,
                        'setBaseTaxAmount' => 0,
                        'setGrandTotal' => 10.00,
                        'setBaseGrandTotal' => 15.00
                    ]
                )
            ],
            'trial payment' => [
                PaymentInfo::PAYMENT_TYPE_TRIAL,
                $this->createProfileMock(
                    [
                        'getProfileCurrencyCode' => 'EUR',
                        'getBaseCurrencyCode' => 'USD',
                        'getTrialSubtotal' => 10.00,
                        'getBaseTrialSubtotal' => 15.00,
                        'getShippingAmount' => 5.00,
                        'getBaseShippingAmount' => 7.50,
                        'getTrialTaxAmount' => 2.00,
                        'getBaseTrialTaxAmount' => 3.00
                    ]
                ),
                $this->createPaymentInfoMock(
                    [
                        'setPaymentType' => PaymentInfo::PAYMENT_TYPE_TRIAL,
                        'setCurrencyCode' => 'EUR',
                        'setBaseCurrencyCode' => 'USD',
                        'setAmount' => 10.00,
                        'setBaseAmount' => 15.00,
                        'setShippingAmount' => 5.00,
                        'setBaseShippingAmount' => 7.50,
                        'setTaxAmount' => 2.00,
                        'setBaseTaxAmount' => 3.00,
                        'setGrandTotal' => 17.00,
                        'setBaseGrandTotal' => 25.50
                    ]
                )
            ],
            'regular payment' => [
                PaymentInfo::PAYMENT_TYPE_REGULAR,
                $this->createProfileMock(
                    [
                        'getProfileCurrencyCode' => 'EUR',
                        'getBaseCurrencyCode' => 'USD',
                        'getSubtotal' => 10.00,
                        'getBaseSubtotal' => 15.00,
                        'getShippingAmount' => 5.00,
                        'getBaseShippingAmount' => 7.50,
                        'getTaxAmount' => 2.00,
                        'getBaseTaxAmount' => 3.00,
                        'getGrandTotal' => 17.00,
                        'getBaseGrandTotal' => 25.50
                    ]
                ),
                $this->createPaymentInfoMock(
                    [
                        'setPaymentType' => PaymentInfo::PAYMENT_TYPE_REGULAR,
                        'setCurrencyCode' => 'EUR',
                        'setBaseCurrencyCode' => 'USD',
                        'setAmount' => 10.00,
                        'setBaseAmount' => 15.00,
                        'setShippingAmount' => 5.00,
                        'setBaseShippingAmount' => 7.50,
                        'setTaxAmount' => 2.00,
                        'setBaseTaxAmount' => 3.00,
                        'setGrandTotal' => 17.00,
                        'setBaseGrandTotal' => 25.50
                    ]
                )
            ]
        ];
    }
}
