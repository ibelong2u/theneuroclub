<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Core;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\SubscriptionEngine;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Subscription\Status\Management as StatusManagement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Core\SubscriptionEngine
 */
class SubscriptionEngineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubscriptionEngine
     */
    private $engine;

    /**
     * @var ProfileRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileRepositoryMock;

    /**
     * @var StatusManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusManagementMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->profileRepositoryMock = $this->getMockForAbstractClass(ProfileRepositoryInterface::class);
        $this->statusManagementMock = $this->createMock(StatusManagement::class);
        $this->engine = $objectManager->getObject(
            SubscriptionEngine::class,
            [
                'profileRepository' => $this->profileRepositoryMock,
                'statusManagement' => $this->statusManagementMock
            ]
        );
    }

    public function testChangeStatus()
    {
        $referenceId = 'reference_id';
        $action = 'activate';
        $status = 'active';

        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);

        $this->profileRepositoryMock->expects($this->once())
            ->method('getByReferenceId')
            ->with($referenceId)
            ->willReturn($profileMock);
        $this->statusManagementMock->expects($this->once())
            ->method('changeStatus')
            ->with($profileMock, $action)
            ->willReturn($status);

        $this->assertEquals($status, $this->engine->changeStatus($referenceId, $action));
    }
}
