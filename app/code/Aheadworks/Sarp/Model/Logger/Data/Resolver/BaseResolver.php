<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Resolver;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\Logger\Data\ResolverInterface;
use Magento\Framework\DataObject;

/**
 * Class BaseResolver
 * @package Aheadworks\Sarp\Model\Logger\Data\Resolver
 */
abstract class BaseResolver implements ResolverInterface
{
    /**
     * Init entry data by common values
     *
     * @param ProfileInterface|DataObject $object
     * @return array
     */
    protected function initEntryData($object)
    {
        $data = [
            'profile_id' => $object->getProfileId(),
            'engine_code' => $object->getEngineCode()
        ];
        if ($this->isProfileBelongsToRegisteredCustomer($object)) {
            $data = array_merge($data, $this->getDataForRegisteredCustomer($object));
        } else {
            $data = array_merge($data, $this->getDataForGuest($object));
        }
        return $data;
    }

    /**
     * Check if profile belongs to registered customer
     *
     * @param ProfileInterface|DataObject $object
     * @return bool
     */
    protected function isProfileBelongsToRegisteredCustomer($object)
    {
        return !empty($object->getCustomerId());
    }

    /**
     * Retrieves data for registered customer
     *
     * @param ProfileInterface|DataObject $object
     * @return array
     */
    protected function getDataForRegisteredCustomer($object)
    {
        $data = [];
        $data['customer_id'] = $object->getCustomerId();
        return  $data;
    }

    /**
     * Retrieves data for guest
     *
     * @param ProfileInterface|DataObject $object
     * @return array
     */
    protected function getDataForGuest($object)
    {
        $data = [];
        $data['customer_email'] = $object->getCustomerEmail();
        $data['customer_fullname'] = $this->getCustomerFullname($object);
        return $data;
    }

    /**
     * Retrieves customer full name
     *
     * @param ProfileInterface|DataObject $object
     * @return string
     */
    protected function getCustomerFullname($object)
    {
        $customerFullname = '';

        if (!empty($object->getCustomerPrefix())) {
            $customerFullname .= $object->getCustomerPrefix() . ' ';
        }
        $customerFullname .= $object->getCustomerFirstname();
        if (!empty($object->getCustomerMiddlename())) {
            $customerFullname .= $object->getCustomerMiddlename() . ' ';
        }
        $customerFullname .= ' ' . $object->getCustomerLastname();
        if (!empty($object->getCustomerSuffix())) {
            $customerFullname .= $object->getCustomerSuffix() . ' ';
        }

        return $customerFullname;
    }
}
