<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart\Totals;

use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\CollectorInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Totals\CollectorsList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Totals\CollectorsList
 */
class CollectorsListTest extends \PHPUnit\Framework\TestCase
{
    const COLLECTOR_CODE = 'collector_code';
    const COLLECTOR_SORT_ORDER = 0;
    const COLLECTOR_CLASS_NAME = 'CollectorClassName';

    /**
     * @var CollectorsList
     */
    private $collectorsList;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->collectorsList = $objectManager->getObject(
            CollectorsList::class,
            [
                'objectManager' => $this->objectManagerMock,
                'collectors' => [
                    self::COLLECTOR_CODE => [
                        'sortOrder' => self::COLLECTOR_SORT_ORDER,
                        'className' => self::COLLECTOR_CLASS_NAME
                    ]
                ]
            ]
        );
    }

    public function testGetCollectors()
    {
        $collectorMock = $this->getMockForAbstractClass(CollectorInterface::class);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(self::COLLECTOR_CLASS_NAME)
            ->willReturn($collectorMock);
        $this->collectorsList->getCollectors();
        $this->assertEquals([self::COLLECTOR_CODE => $collectorMock], $this->collectorsList->getCollectors());
    }

    /**
     * @param array $definition1
     * @param array $definition2
     * @param int $result
     * @dataProvider sortCollectorsDataProvider
     */
    public function testSortCollectors($definition1, $definition2, $result)
    {
        $class = new \ReflectionClass($this->collectorsList);
        $method = $class->getMethod('sortCollectors');
        $method->setAccessible(true);

        $this->assertEquals(
            $result,
            $method->invokeArgs($this->collectorsList, [$definition1, $definition2])
        );
    }

    /**
     * @return array
     */
    public function sortCollectorsDataProvider()
    {
        return [
            [['sortOrder' => 1], ['sortOrder' => 1], 0],
            [['sortOrder' => 1], ['sortOrder' => 2], -1],
            [['sortOrder' => 2], ['sortOrder' => 1], 1],
            [[], ['sortOrder' => 1], 0],
            [['sortOrder' => 1], [], 0]
        ];
    }
}
