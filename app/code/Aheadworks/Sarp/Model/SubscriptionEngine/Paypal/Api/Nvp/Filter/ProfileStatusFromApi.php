<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter;

use Aheadworks\Sarp\Model\Profile\Source\Status as ProfileStatus;

/**
 * Class ProfileStatusFromApi
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Filter
 */
class ProfileStatusFromApi implements \Zend_Filter_Interface
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        switch ($value) {
            case 'Active':
            case 'ActiveProfile':
                return ProfileStatus::ACTIVE;
            case 'Pending':
            case 'PendingProfile':
                return ProfileStatus::PENDING;
            case 'Cancelled':
                return ProfileStatus::CANCELLED;
            case 'Suspended':
                return ProfileStatus::SUSPENDED;
            case 'Expired':
                return ProfileStatus::EXPIRED;
            default:
                break;
        }
        return null;
    }
}
