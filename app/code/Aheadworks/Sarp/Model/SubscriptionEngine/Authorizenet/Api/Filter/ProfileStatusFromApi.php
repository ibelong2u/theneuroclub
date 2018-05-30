<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Filter;

use Aheadworks\Sarp\Model\Profile\Source\Status as ProfileStatus;

/**
 * Class ProfileStatusFromApi
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Filter
 */
class ProfileStatusFromApi implements \Zend_Filter_Interface
{
    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        switch ($value) {
            case 'active':
                return ProfileStatus::ACTIVE;
            case 'expired':
                return ProfileStatus::EXPIRED;
            case 'suspended':
                return ProfileStatus::SUSPENDED;
            case 'canceled':
                return ProfileStatus::CANCELLED;
            case 'terminated':
                return ProfileStatus::TERMINATED;
            default:
                break;
        }
        return null;
    }
}
