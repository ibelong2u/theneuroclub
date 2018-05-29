<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface EngineInterface
 * @package Aheadworks\Sarp\Model\SubscriptionEngine
 * @api
 */
interface EngineInterface
{
    /**
     * Submit recurring profile
     *
     * @param ProfileInterface $profile
     * @param array $additionalParams
     * @param SubscriptionsCartPaymentInterface $paymentInformation
     * @return ProfileInterface
     */
    public function submitProfile(
        ProfileInterface $profile,
        $additionalParams = [],
        SubscriptionsCartPaymentInterface $paymentInformation = null
    );

    /**
     * Update recurring profile
     *
     * @param ProfileInterface $profile
     * @return ProfileInterface
     */
    public function updateProfile(ProfileInterface $profile);

    /**
     * Get recurring profile
     *
     * @param string $referenceId
     * @return array
     */
    public function getProfileData($referenceId);

    /**
     * Perform change status action
     *
     * @param string $referenceId
     * @param string $action
     * @param array $additionalData
     * @return string
     * @throws LocalizedException
     * @throws \Exception
     */
    public function changeStatus($referenceId, $action, $additionalData = []);
}
