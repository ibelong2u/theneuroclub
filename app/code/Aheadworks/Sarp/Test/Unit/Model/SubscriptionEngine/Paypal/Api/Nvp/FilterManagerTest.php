<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Paypal\Api\Nvp;

use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\FilterManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\FilterManager
 */
class FilterManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->filterManager = $objectManager->getObject(
            FilterManager::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testFilterToApi()
    {
        $key = 'fieldName';
        $rawValue = 'fieldValueRaw';
        $value = 'fieldValue';
        $filterClassName = 'Filter';

        $filterMock = $this->getMockForAbstractClass(\Zend_Filter_Interface::class);

        $class = new \ReflectionClass($this->filterManager);
        $property = $class->getProperty('toApiFilterMap');
        $property->setAccessible(true);
        $property->setValue($this->filterManager, [$key => $filterClassName]);

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($filterClassName)
            ->willReturn($filterMock);
        $filterMock->expects($this->once())
            ->method('filter')
            ->with($rawValue)
            ->willReturn($value);

        $this->assertEquals(
            [$key => $value],
            $this->filterManager->filterToApi([$key => $rawValue])
        );
    }

    public function testFilterFromApi()
    {
        $key = 'fieldName';
        $rawValue = 'fieldValueRaw';
        $value = 'fieldValue';
        $filterClassName = 'Filter';

        $filterMock = $this->getMockForAbstractClass(\Zend_Filter_Interface::class);

        $class = new \ReflectionClass($this->filterManager);
        $property = $class->getProperty('fromApiFilterMap');
        $property->setAccessible(true);
        $property->setValue($this->filterManager, [$key => $filterClassName]);

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($filterClassName)
            ->willReturn($filterMock);
        $filterMock->expects($this->once())
            ->method('filter')
            ->with($rawValue)
            ->willReturn($value);

        $this->assertEquals(
            [$key => $value],
            $this->filterManager->filterfromApi([$key => $rawValue])
        );
    }

    public function testGetFilter()
    {
        $filterClassName = 'Filter';

        $filterMock = $this->getMockForAbstractClass(\Zend_Filter_Interface::class);

        $class = new \ReflectionClass($this->filterManager);
        $method = $class->getMethod('getFilter');
        $method->setAccessible(true);

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($filterClassName)
            ->willReturn($filterMock);

        $this->assertEquals(
            $filterMock,
            $method->invokeArgs($this->filterManager, [$filterClassName])
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Filter class FilterNonexistent does not implement required interface.
     */
    public function testGetFilterException()
    {
        $filterClassName = 'FilterNonexistent';

        $filterMock = $this->getMockBuilder($filterClassName)->getMock();

        $class = new \ReflectionClass($this->filterManager);
        $method = $class->getMethod('getFilter');
        $method->setAccessible(true);

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($filterClassName)
            ->willReturn($filterMock);

        $method->invokeArgs($this->filterManager, [$filterClassName]);
    }
}
