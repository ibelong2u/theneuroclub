<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Filter;

use Aheadworks\Sarp\Model\Profile\Source\Status as ProfileStatus;

/**
 * Class ProfileStatusFromApi
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Filter
 */
class ProfileStatusFromApi implements \Zend_Filter_Interface
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        switch ($value) {
            case 'trialing':
                return ProfileStatus::TRAILING;
            case 'active':
                return ProfileStatus::ACTIVE;
            case 'past_due':
                return ProfileStatus::PAST_DUE;
            case 'canceled':
                return ProfileStatus::CANCELLED;
            case 'unpaid':
                return ProfileStatus::UNPAID;
            default:
                break;
        }
        return null;
    }
}
