<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Validator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Sarp\Model\SubscriptionsCart\DateChecker;
use Magento\Framework\Stdlib\DateTime;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Validator
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var DateChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateCheckerMock;

    /**
     * @var SubscriptionsCartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->dateCheckerMock = $this->createMock(DateChecker::class);
        $this->cartMock = $this->getMockForAbstractClass(SubscriptionsCartInterface::class);
        $this->validator = $objectManager->getObject(
            Validator::class,
            [
                'dateChecker' => $this->dateCheckerMock
            ]
        );
    }

    /**
     * Test IsValid method, if validation is true
     *
     * @return void
     */
    public function testIsValid()
    {
        $startDate = '2016-01-10';
        $currentDate = '2016-01-10';

        $this->cartMock->expects($this->exactly(3))
            ->method('getStartDate')
            ->willReturn($startDate);

        $this->dateCheckerMock->expects($this->exactly(1))
            ->method('getCurrentDate')
            ->willReturn(new \Zend_Date($currentDate, DateTime::DATE_INTERNAL_FORMAT, 'en_US'));

        $this->assertTrue($this->validator->isValid($this->cartMock));
        $this->assertEmpty($this->validator->getMessages());
    }

    /**
     * Test IsValid method, if startDate not specified
     *
     * @return void
     */
    public function testIsValidStartDateNotSpecified()
    {
        $this->cartMock->expects($this->once())
            ->method('getStartDate')
            ->willReturn(null);

        $this->assertTrue($this->validator->isValid($this->cartMock));
        $this->assertEmpty($this->validator->getMessages());
    }

    /**
     * Test IsValid method, if startDate not correct
     *
     * @return void
     */
    public function testIsValidStartDateIncorrect()
    {
        $startDate = 'incorrect value';
        $expectedMessages = ['Start date is incorrect.'];

        $this->cartMock->expects($this->exactly(2))
            ->method('getStartDate')
            ->willReturn($startDate);

        $this->assertFalse($this->validator->isValid($this->cartMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * Test IsValid method, if startDate in the past
     *
     * @return void
     */
    public function testIsValidStartDateInThePast()
    {
        $startDate = '2016-01-10';
        $currentDate = '2016-01-11';
        $expectedMessages = ['Start date must be in future.'];

        $this->cartMock->expects($this->exactly(3))
            ->method('getStartDate')
            ->willReturn($startDate);

        $this->dateCheckerMock->expects($this->exactly(1))
            ->method('getCurrentDate')
            ->willReturn(new \Zend_Date($currentDate, DateTime::DATE_INTERNAL_FORMAT, 'en_US'));

        $this->assertFalse($this->validator->isValid($this->cartMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }
}
