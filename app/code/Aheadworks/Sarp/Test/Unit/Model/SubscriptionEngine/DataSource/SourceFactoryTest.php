<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\DataSource;

use Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\SourceFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\DataSource\SourceFactory
 */
class SourceFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SourceFactory
     */
    private $factory;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->factory = $objectManager->getObject(
            SourceFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $sourceClassName = 'DataSource';

        $sourceMock = $this->getMockForAbstractClass(OptionSourceInterface::class);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($sourceClassName)
            ->willReturn($sourceMock);
        $this->assertEquals($sourceMock, $this->factory->create($sourceClassName));
    }
}
