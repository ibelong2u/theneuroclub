<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Filter;

use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class FormatDate
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Filter
 */
class FormatDate implements \Zend_Filter_Interface
{
    /**
     * Date format
     */
    const DATE_FORMAT = 'Y-m-d';

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @param TimezoneInterface $localeDate
     */
    public function __construct(TimezoneInterface $localeDate)
    {
        $this->localeDate = $localeDate;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        $now = $this->localeDate->date(null, null, false);
        $date = $this->localeDate->date($value, null, false);
        if ($now > $date) {
            $date = $now;
        }
        $date->setTimezone(new \DateTimeZone('US/Mountain'));
        return $date->format(self::DATE_FORMAT);
    }
}
