<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart;

use Aheadworks\Sarp\Model\Session as SarpSession;
use Aheadworks\Sarp\Model\SubscriptionsCart\SuccessValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\SuccessValidator
 */
class SuccessValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SuccessValidator
     */
    private $validator;

    /**
     * @var SarpSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sarpSessionMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->sarpSessionMock = $this->createMock(SarpSession::class);
        $this->validator = $objectManager->getObject(
            SuccessValidator::class,
            ['sarpSession' => $this->sarpSessionMock]
        );
    }

    /**
     * @param int|null $lastSuccessCartId
     * @param int|null $lastProfileId
     * @param bool $result
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($lastSuccessCartId, $lastProfileId, $result)
    {
        $this->sarpSessionMock->expects(!$lastSuccessCartId ? $this->once() : $this->exactly(2))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getLastSuccessCartId', [], $lastSuccessCartId],
                    ['getLastProfileId', [], $lastProfileId]
                ]
            );

        $this->assertEquals($result, $this->validator->isValid());
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [1, 2, true],
            [1, null, false],
            [null, 1, false],
            [null, null, false]
        ];
    }
}
