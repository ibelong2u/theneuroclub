<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api;

use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Filter\PeriodUnit;
use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Filter\ProfileStatusFromApi;
use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Filter\ToCents;
use Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Filter\ToLowercase;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class FilterManager
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api
 */
class FilterManager
{
    /**
     * @var array
     */
    private $toApiFilterMap = [
        'amount' => ToCents::class,
        'interval' => PeriodUnit::class,
        'currency' => ToLowercase::class
    ];

    /**
     * @var array
     */
    private $fromApiFilterMap = [
        'profile_status' => ProfileStatusFromApi::class
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Filter request data to api
     *
     * @param array $data
     * @return array
     */
    public function filterToApi($data)
    {
        foreach ($data as $key => $value) {
            if (isset($this->toApiFilterMap[$key])) {
                $data[$key] = $this->getFilter($this->toApiFilterMap[$key])->filter($value);
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
