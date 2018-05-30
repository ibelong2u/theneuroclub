<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Adminhtml\Subscriptionplan;

use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Class PostDataProcessor
 * @package Aheadworks\Sarp\Controller\Adminhtml\Subscriptionplan
 */
class PostDataProcessor
{
    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(BooleanUtils $booleanUtils)
    {
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * Prepare entity data for save
     *
     * @param array $data
     * @return array
     */
    public function prepareEntityData($data)
    {
        if (empty($data['total_billing_cycles'])) {
            $data['total_billing_cycles'] = 0;
        }
        if ($data['start_date_day_of_month'] == '') {
            $data['start_date_day_of_month'] = null;
        }
        if ($data['is_initial_fee_enabled'] == '') {
            $data['is_initial_fee_enabled'] = false;
        }
        if ($data['is_trial_period_enabled'] == '') {
            $data['is_trial_period_enabled'] = false;
        }
        if (isset($data['descriptions'])) {
            foreach ($data['descriptions'] as $index => $descriptionData) {
                $isRemoved = $this->booleanUtils->toBoolean($descriptionData['removed']);
                if ($isRemoved) {
                    unset($data['descriptions'][$index]);
                }
            }
        }
        $isTrialEnabled = $this->booleanUtils->toBoolean($data['is_trial_period_enabled']);
        if (!$isTrialEnabled) {
            $data['trial_total_billing_cycles'] = 0;
        }
        return $data;
    }
}
