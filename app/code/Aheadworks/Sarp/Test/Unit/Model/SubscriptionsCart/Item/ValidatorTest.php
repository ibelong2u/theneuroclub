<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart\Item;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartItemInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Item\QuantityValidator;
use Aheadworks\Sarp\Model\SubscriptionsCart\Item\Validator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Item\Validator
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var QuantityValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quantityValidatorMock;

    /**
     * @var array
     */
    private $itemData = [
        'getName' => 'Item name',
        'getBuyRequest' => 'a:1:{s:10:"product_id";s:1:"2";}'
    ];

    /**
     * Init mocks for tests
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->quantityValidatorMock = $this->createMock(QuantityValidator::class);
        $this->validator = $objectManager->getObject(
            Validator::class,
            ['quantityValidator' => $this->quantityValidatorMock]
        );
    }

    /**
     * @param SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($itemMock, $expectedResult, $expectedMessages)
    {
        $this->quantityValidatorMock->expects($this->any())
            ->method('isValid')
            ->with($itemMock)
            ->willReturn(true);

        $this->assertEquals($expectedResult, $this->validator->isValid($itemMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * @param SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock
     * @param bool $isValid
     * @param array $messages
     * @dataProvider isValidQuantityDataProvider
     */
    public function testIsValidQuantity($itemMock, $isValid, $messages)
    {
        $this->quantityValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($itemMock)
            ->willReturn($isValid);
        $this->quantityValidatorMock->expects($this->any())
            ->method('getMessages')
            ->willReturn($messages);

        $this->assertEquals($isValid, $this->validator->isValid($itemMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }

    /**
     * Create cart item mock and optionally modify getter result
     *
     * @param string|null $methodModify
     * @param mixed|null $valueModify
     * @return SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createItemMock($methodModify = null, $valueModify = null)
    {
        /** @var SubscriptionsCartItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->getMockForAbstractClass(SubscriptionsCartItemInterface::class);
        foreach ($this->itemData as $method => $value) {
            if ($method != $methodModify) {
                $itemMock->expects($this->any())
                    ->method($method)
                    ->willReturn($value);
            } else {
                $itemMock->expects($this->any())
                    ->method($methodModify)
                    ->willReturn($valueModify);
            }
        }
        return $itemMock;
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            'correct data' => [$this->createItemMock(), true, []],
            'missing name' => [
                $this->createItemMock('getName', null),
                false,
                ['Name is required.']
            ],
            'missing buy request' => [
                $this->createItemMock('getBuyRequest', null),
                false,
                ['Buy request is required.']
            ]
        ];
    }

    /**
     * @return array
     */
    public function isValidQuantityDataProvider()
    {
        return [
            'qty is valid' => [$this->createItemMock(), true, []],
            'qty is not valid' => [$this->createItemMock(), false, ['Qty is incorrect.']]
        ];
    }
}
