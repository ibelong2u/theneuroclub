<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model;

use Aheadworks\Sarp\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Test for \Aheadworks\Sarp\Model\Config
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->config = $objectManager->getObject(
            Config::class,
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsApplyTaxOnTrialAmount($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_APPLY_TAX_ON_TRIAL_AMOUNT, ScopeInterface::SCOPE_WEBSITE)
            ->willReturn($value);
        $this->assertSame($value, $this->config->isApplyTaxOnTrialAmount());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsApplyTaxOnShippingAmount($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_APPLY_TAX_ON_SHIPPING_AMOUNT, ScopeInterface::SCOPE_WEBSITE)
            ->willReturn($value);
        $this->assertSame($value, $this->config->isApplyTaxOnShippingAmount());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsDisplayYouSaveXPercentsOnProductPage($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_DISPLAY_YOU_SAVE_X_PERCENTS, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);
        $this->assertSame($value, $this->config->isDisplayYouSaveXPercentsOnProductPage());
    }

    public function testGetTooltipNearSubscriptionButtonContent()
    {
        $tooltipContent = '<p>Tooltip content</p>';

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_TOOLTIP_NEAR_SUBSCRIPTION_BUTTON, ScopeInterface::SCOPE_STORE)
            ->willReturn($tooltipContent);
        $this->assertEquals(
            $tooltipContent,
            $this->config->getTooltipNearSubscriptionButtonContent()
        );
    }

    /**
     * @return array
     */
    public function boolDataProvider()
    {
        return [[true], [false]];
    }

    public function testGetKeepLogForDaysValue()
    {
        $value = 89;

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_KEEP_LOG_FOR_DAYS)
            ->willReturn($value);
        $this->assertEquals(
            $value,
            $this->config->getKeepLogForDaysValue()
        );
    }
}
