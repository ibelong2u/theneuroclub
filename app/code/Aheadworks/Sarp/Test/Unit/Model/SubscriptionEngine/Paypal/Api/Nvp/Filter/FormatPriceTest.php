<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter;

use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\FormatPrice as PriceFilter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\FormatPrice
 */
class FormatPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PriceFilter
     */
    private $filter;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->filter = $objectManager->getObject(PriceFilter::class);
    }

    /**
     * @param float $inputValue
     * @param string $outputValue
     * @dataProvider filterDataProvider
     */
    public function testFilter($inputValue, $outputValue)
    {
        $this->assertEquals($outputValue, $this->filter->filter($inputValue));
    }

    /**
     * @return array
     */
    public function filterDataProvider()
    {
        return [[10, '10.00'], [10.05, '10.05'], [10.055, '10.05']];
    }
}
