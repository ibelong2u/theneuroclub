<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Core\Generator;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\Profile;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Generator\ReferenceId;
use Magento\Framework\DB\Sequence\SequenceInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesSequence\Model\Manager as SequenceManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Core\Generator\ReferenceId
 */
class ReferenceIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReferenceId
     */
    private $generator;

    /**
     * @var SequenceManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sequenceManagerMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->sequenceManagerMock = $this->createMock(SequenceManager::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->generator = $objectManager->getObject(
            ReferenceId::class,
            [
                'sequenceManager' => $this->sequenceManagerMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    public function testGetReferenceId()
    {
        $storeId = 1;
        $storeGroupId = 2;
        $defaultStoreId = 3;
        $value = '000000001';

        /** @var ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $groupMock = $this->getMockForAbstractClass(GroupInterface::class);
        $sequenceMock = $this->getMockForAbstractClass(SequenceInterface::class);

        $profileMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getStoreGroupId')
            ->willReturn($storeGroupId);
        $this->storeManagerMock->expects($this->once())
            ->method('getGroup')
            ->with($storeGroupId)
            ->willReturn($groupMock);
        $groupMock->expects($this->once())
            ->method('getDefaultStoreId')
            ->willReturn($defaultStoreId);
        $this->sequenceManagerMock->expects($this->once())
            ->method('getSequence')
            ->with(Profile::ENTITY, $defaultStoreId)->willReturn($sequenceMock);
        $sequenceMock->expects($this->once())
            ->method('getNextValue')
            ->willReturn($value);

        $this->assertEquals($value, $this->generator->getReferenceId($profileMock));
    }
}
