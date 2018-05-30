<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter;

use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\ProfileStatusFromApi as ProfileStatusFilter;
use Aheadworks\Sarp\Model\Profile\Source\Status as ProfileStatus;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\ProfileStatusFromApi
 */
class ProfileStatusFromApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProfileStatusFilter
     */
    private $filter;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->filter = $objectManager->getObject(ProfileStatusFilter::class);
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
            ['Active', ProfileStatus::ACTIVE],
            ['ActiveProfile', ProfileStatus::ACTIVE],
            ['Pending', ProfileStatus::PENDING],
            ['PendingProfile', ProfileStatus::PENDING],
            ['Cancelled', ProfileStatus::CANCELLED],
            ['Suspended', ProfileStatus::SUSPENDED],
            ['Expired', ProfileStatus::EXPIRED]
        ];
    }
}
