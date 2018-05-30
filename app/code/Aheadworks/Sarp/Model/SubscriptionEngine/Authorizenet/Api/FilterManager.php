<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api;

use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Filter\FormatDate;
use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Filter\PeriodUnit;
use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Filter\ProfileStatusFromApi;
use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Filter\TotalBillingCycles;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class FilterManager
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api
 */
class FilterManager
{
    /**
     * @var array
     */
    private $toApiFilterMap = [
        'subscription/name' => 'AwSarpAuthorizenetFilterCutStringTo50',
        'subscription/paymentSchedule/interval/unit' => PeriodUnit::class,
        'subscription/paymentSchedule/startDate' => FormatDate::class,
        'subscription/paymentSchedule/totalOccurrences' => TotalBillingCycles::class,
        'subscription/billTo/firstName' => 'AwSarpAuthorizenetFilterCutStringTo50',
        'subscription/billTo/lastName' => 'AwSarpAuthorizenetFilterCutStringTo50',
        'subscription/billTo/address' => 'AwSarpAuthorizenetFilterCutStringTo60',
        'subscription/billTo/country' => 'AwSarpAuthorizenetFilterCutStringTo60',
        'subscription/billTo/city' => 'AwSarpAuthorizenetFilterCutStringTo40',
        'subscription/shipTo/firstName' => 'AwSarpAuthorizenetFilterCutStringTo50',
        'subscription/shipTo/lastName' => 'AwSarpAuthorizenetFilterCutStringTo50',
        'subscription/shipTo/address' => 'AwSarpAuthorizenetFilterCutStringTo60',
        'subscription/shipTo/country' => 'AwSarpAuthorizenetFilterCutStringTo60',
        'subscription/shipTo/city' => 'AwSarpAuthorizenetFilterCutStringTo40'
    ];

    /**
     * @var array
     */
    private $fromApiFilterMap = [
        'status' => ProfileStatusFromApi::class
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ArrayManager $arrayManager
    ) {
        $this->objectManager = $objectManager;
        $this->arrayManager = $arrayManager;
    }

    /**
     * Filter request data to api
     *
     * @param array $data
     * @return array
     */
    public function filterToApi($data)
    {
        foreach ($this->toApiFilterMap as $path => $filterClass) {
            if ($this->arrayManager->exists($path, $data)) {
                $filter = $this->getFilter($filterClass);
                $data = $this->arrayManager->replace(
                    $path,
                    $data,
                    $filter->filter($this->arrayManager->get($path, $data))
                );
            }
        }
        return $data;
    }

    /**
     * Filter response data from api
     *
     * @param array $data
     * @return array
     */
    public function filterFromApi($data)
    {
        foreach ($data as $key => $value) {
            if (isset($this->fromApiFilterMap[$key])) {
                $data[$key] = $this->getFilter($this->fromApiFilterMap[$key])->filter($value);
            }
        }
        return $data;
    }

    /**
     * Get filter instance
     *
     * @param string $filterClassName
     * @return \Zend_Filter_Interface
     * @throws \Exception
     */
    private function getFilter($filterClassName)
    {
        $filterInstance = $this->objectManager->get($filterClassName);
        if (!$filterInstance instanceof \Zend_Filter_Interface) {
            throw new \Exception(
                sprintf('Filter class %s does not implement required interface.', $filterClassName)
            );
        }
        return $filterInstance;
    }
}
