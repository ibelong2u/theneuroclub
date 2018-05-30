<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp;

use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp as ApiNvp;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\Action;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\FilterInt;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\FormatDate;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\FormatPrice;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\PeriodUnit;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter\ProfileStatusFromApi;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class FilterManager
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp
 */
class FilterManager
{
    /**
     * @var array
     */
    private $toApiFilterMap = [
        'PAYMENTREQUEST_0_AMT' => FormatPrice::class,
        'L_BILLINGAGREEMENTDESCRIPTION0' => 'AwSarpPayPalNvpFilterCutStringTo127',
        'NOSHIPPING' => FilterInt::class,
        'PROFILESTARTDATE' => FormatDate::class,
        'DESC' => 'AwSarpPayPalNvpFilterCutStringTo127',
        'BILLINGPERIOD' => PeriodUnit::class,
        'BILLINGFREQUENCY' => FilterInt::class,
        'TOTALBILLINGCYCLES' => FilterInt::class,
        'AMT' => FormatPrice::class,
        'TRIALBILLINGPERIOD' => PeriodUnit::class,
        'TRIALBILLINGFREQUENCY' => FilterInt::class,
        'TRIALTOTALBILLINGCYCLES' => FilterInt::class,
        'TRIALAMT' => FormatPrice::class,
        'SHIPPINGAMT' => FormatPrice::class,
        'TAXAMT' => FormatPrice::class,
        'ACTION' => Action::class
    ];

    /**
     * @var array
     */
    private $fromApiFilterMap = [
        'profile_status' => ProfileStatusFromApi::class,
        'status' => ProfileStatusFromApi::class
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
