<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\Checkout;

use Aheadworks\Sarp\Model\Checkout\CompositeConfigProvider;
use Aheadworks\Sarp\Model\Checkout\ConfigProviderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\Checkout\CompositeConfigProvider
 */
class CompositeConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompositeConfigProvider
     */
    private $compositeConfigProvider;

    /**
     * @var ConfigProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configProvider1;

    /**
     * @var ConfigProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configProvider2;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configProvider1 = $this->getMockForAbstractClass(ConfigProviderInterface::class);
        $this->configProvider2 = $this->getMockForAbstractClass(ConfigProviderInterface::class);
        $this->compositeConfigProvider = $objectManager->getObject(
            CompositeConfigProvider::class,
            [
                'configProviders' => [$this->configProvider1, $this->configProvider2]
            ]
        );
    }

    public function testGetConfig()
    {
        $this->configProvider1->expects($this->once())
            ->method('getConfig')
            ->willReturn(['key1' => 'value1']);
        $this->configProvider2->expects($this->once())
            ->method('getConfig')
            ->willReturn(['key2' => 'value2']);
        $this->assertEquals(
            [
                'key1' => 'value1',
                'key2' => 'value2'
            ],
            $this->compositeConfigProvider->getConfig()
        );
    }
}
