<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Api;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;

/**
 * Profile management interface
 * @api
 */
interface ProfileManagementInterface
{
    /**
     * Create order
     *
     * @param ProfileInterface $profile
     * @param ProfilePaymentInfoInterface $paymentInfo
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function createOrder(ProfileInterface $profile, ProfilePaymentInfoInterface $paymentInfo);

    /**
     * Perform change status action
     *
     * @param int $profileId
     * @param string $action
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Aheadworks\Sarp\Api\Exception\OperationIsNotSupportedExceptionInterface
     */
    public function changeStatusAction($profileId, $action);

    /**
     * Retrieve data from API and refresh profile data
     *
     * @param int $profileId
     * @return ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refreshProfileData($profileId);

    /**
     * @param int $profileId
     * @param string $addressType
     * @param ProfileAddressInterface $address
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateProfileAddress($profileId, $addressType, ProfileAddressInterface $address);
}
