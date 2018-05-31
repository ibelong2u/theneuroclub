<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class DateChecker
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 */
class DateChecker
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param TimezoneInterface $timezone
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        TimezoneInterface $timezone,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->timezone = $timezone;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get current date according to current website timezone
     *
     * @return \Zend_Date
     */
    public function getCurrentDate()
    {
        $websiteTimezone = $this->scopeConfig->getValue(
            $this->timezone->getDefaultTimezonePath(),
            ScopeInterface::SCOPE_WEBSITE,
            null
        );
        $nowDate = new \DateTime('now', new \DateTimeZone($websiteTimezone));
        $currentDate = new \Zend_Date($nowDate->format('Y-m-d'), DateTime::DATE_INTERNAL_FORMAT, 'en_US');
        $currentDate->setHour(0)->setMinute(0)->setSecond(0);

        return $currentDate;
    }

    /**
     * Get current date according to current website timezone (formatted)
     *
     * @return string
     */
    public function getCurrentDateFrontend()
    {
        return $this->getCurrentDate()->toString('MM/dd/y');
    }
}
