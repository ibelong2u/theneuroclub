<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart\Address;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Mapper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\Address\Mapper
 */
class MapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Mapper
     */
    private $mapper;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->mapper = $objectManager->getObject(Mapper::class);
    }

    public function testEntityToDatabase()
    {
        $data = ['street' => ['street line 1', 'street line 2']];
        $this->assertEquals(
            ['street' => 'street line 1\nstreet line 2'],
            $this->mapper->entityToDatabase(SubscriptionsCartAddressInterface::class, $data)
        );
    }

    public function testDatabaseToEntity()
    {
        $data = ['street' => 'street line 1\nstreet line 2'];
        $this->assertEquals(
            ['street' => ['street line 1', 'street line 2']],
            $this->mapper->databaseToEntity(SubscriptionsCartAddressInterface::class, $data)
        );
    }
}
