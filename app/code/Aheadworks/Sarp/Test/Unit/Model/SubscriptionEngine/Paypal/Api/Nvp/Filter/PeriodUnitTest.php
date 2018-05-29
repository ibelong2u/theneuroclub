<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter;

use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\PeriodUnit as PeriodUnitFilter;
use Aheadworks\Sarp\Model\SubscriptionPlan\Source\BillingPeriod as PeriodUnitSource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\PeriodUnit
 */
class PeriodUnitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PeriodUnitFilter
     */
    private $filter;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->filter = $objectManager->getObject(PeriodUnitFilter::class);
    }

    /**
     * @param string $inputValue
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
        return [
            [PeriodUnitSource::DAY, 'Day'],
            [PeriodUnitSource::WEEK, 'Week'],
            [PeriodUnitSource::SEMI_MONTH, 'SemiMonth'],
            [PeriodUnitSource::MONTH, 'Month'],
            [PeriodUnitSource::YEAR, 'Year']
        ];
    }
}
