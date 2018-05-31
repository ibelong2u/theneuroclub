<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter;

use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Config;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class FormatDate
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter
 */
class FormatDate implements \Zend_Filter_Interface
{
    /**
     * Date format
     */
    const DATE_FORMAT = 'c';

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param TimezoneInterface $localeDate
     * @param Config $config
     */
    public function __construct(
        TimezoneInterface $localeDate,
        Config $config
    ) {
        $this->localeDate = $localeDate;
        $this->config = $config;
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
        $date->setTimezone(new \DateTimeZone($this->config->getMerchantTimezone()));
        return $date->format(self::DATE_FORMAT);
    }
}
