<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Authorizenet;

use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\RequestBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\RequestBuilder
 */
class RequestBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RequestBuilder
     */
    private $builder;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->builder = $objectManager->getObject(RequestBuilder::class);
    }

    public function testBuild()
    {
        $method = 'ARBCreateSubscriptionRequest';
        $data = ['fieldName' => ['subFieldName' => 'fieldValue']];
        $expectedResult = '<?xml version="1.0" '
            . 'encoding="utf-8"?><ARBCreateSubscriptionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">'
            . '<fieldName>'
            . '<subFieldName>fieldValue</subFieldName>'
            . '</fieldName>'
            . '</ARBCreateSubscriptionRequest>';

        $this->assertEquals($expectedResult, $this->builder->build($method, $data));
    }
}
