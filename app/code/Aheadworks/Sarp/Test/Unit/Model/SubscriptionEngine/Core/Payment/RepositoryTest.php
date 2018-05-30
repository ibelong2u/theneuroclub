<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Core\Payment;

use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment as PaymentResource;
use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment\Collection;
use Aheadworks\Sarp\Model\ResourceModel\CoreEngine\Payment\CollectionFactory;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\Repository;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\Repository\CriteriaApplier;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\Repository
 */
class RepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var PaymentResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var CriteriaApplier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $criteriaApplierMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resourceMock = $this->createMock(PaymentResource::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->criteriaApplierMock = $this->createMock(CriteriaApplier::class);
        $this->repository = $objectManager->getObject(
            Repository::class,
            [
                'resource' => $this->resourceMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'criteriaApplier' => $this->criteriaApplierMock
            ]
        );
    }

    public function testSave()
    {
        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);
        $this->assertEquals($paymentMock, $this->repository->save($paymentMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Unable to save payment instance.
     */
    public function testSaveException()
    {
        $exceptionMessage = 'Unable to save payment instance.';
        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($paymentMock)
            ->willThrowException(new \Exception($exceptionMessage));
        $this->assertEquals($paymentMock, $this->repository->save($paymentMock));
    }

    public function testGetList()
    {
        $criteria = [['field', 'value']];
        $items = [
            $this->createMock(Payment::class),
            $this->createMock(Payment::class)
        ];

        $collectionMock = $this->createMock(Collection::class);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $this->criteriaApplierMock->expects($this->once())
            ->method('apply')
            ->with($collectionMock, $criteria);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn($items);

        $this->assertEquals($items, $this->repository->getList($criteria));
    }

    /**
     * @param int $collectionSize
     * @param bool $result
     * @dataProvider hasDataProvider
     */
    public function testHas($collectionSize, $result)
    {
        $criteria = [['field', 'value']];

        $collectionMock = $this->createMock(Collection::class);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $this->criteriaApplierMock->expects($this->once())
            ->method('apply')
            ->with($collectionMock, $criteria);
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);

        $this->assertEquals($result, $this->repository->has($criteria));
    }

    /**
     * @return array
     */
    public function hasDataProvider()
    {
        return [[1, true], [0, false]];
    }
}
