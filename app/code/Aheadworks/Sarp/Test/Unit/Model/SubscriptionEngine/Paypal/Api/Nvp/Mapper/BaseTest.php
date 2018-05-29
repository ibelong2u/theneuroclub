<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Paypal\Api\Nvp\Mapper;

use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Mapper\Base as BaseMapper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Mapper\Base
 */
class BaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BaseMapper
     */
    private $mapper;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->mapper = $objectManager->getObject(BaseMapper::class);
    }

    public function testToApi()
    {
        $method = 'CreateRecurringPaymentsProfile';
        $fromFieldName = 'fieldName';
        $toFieldName = 'apiFieldName';
        $fieldValue = 'fieldValue';

        $class = new \ReflectionClass($this->mapper);

        $toApiMapsProperty = $class->getProperty('toApiMaps');
        $toApiMapsProperty->setAccessible(true);
        $toApiMapsProperty->setValue($this->mapper, [$method => [$fromFieldName => $toFieldName]]);

        $toApiDefaultsProperty = $class->getProperty('toApiDefaults');
        $toApiDefaultsProperty->setAccessible(true);
        $toApiDefaultsProperty->setValue($this->mapper, [$method => []]);

        $this->assertEquals(
            [
                'METHOD' => $method,
                $toFieldName => $fieldValue
            ],
            $this->mapper->toApi($method, [$fromFieldName => $fieldValue])
        );
    }

    public function testToApiDefaultValue()
    {
        $method = 'CreateRecurringPaymentsProfile';
        $fromFieldName = 'fieldName';
        $toFieldName = 'apiFieldName';
        $defaultFieldValue = 'defaultValue';

        $class = new \ReflectionClass($this->mapper);

        $toApiMapsProperty = $class->getProperty('toApiMaps');
        $toApiMapsProperty->setAccessible(true);
        $toApiMapsProperty->setValue($this->mapper, [$method => [$fromFieldName => $toFieldName]]);

        $toApiDefaultsProperty = $class->getProperty('toApiDefaults');
        $toApiDefaultsProperty->setAccessible(true);
        $toApiDefaultsProperty->setValue($this->mapper, [$method => [$toFieldName => $defaultFieldValue]]);

        $this->assertEquals(
            [
                'METHOD' => $method,
                $toFieldName => $defaultFieldValue
            ],
            $this->mapper->toApi($method, [])
        );
    }

    public function testFromApi()
    {
        $method = 'CreateRecurringPaymentsProfile';
        $fromFieldName = 'apiFieldName';
        $toFieldName = 'fieldName';
        $fieldValue = 'fieldValue';

        $class = new \ReflectionClass($this->mapper);

        $toApiMapsProperty = $class->getProperty('fromApiMaps');
        $toApiMapsProperty->setAccessible(true);
        $toApiMapsProperty->setValue($this->mapper, [$method => [$fromFieldName => $toFieldName]]);

        $this->assertEquals(
            [$toFieldName => $fieldValue],
            $this->mapper->fromApi($method, [$fromFieldName => $fieldValue])
        );
    }
}
