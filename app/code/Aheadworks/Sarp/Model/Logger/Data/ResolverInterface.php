<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Magento\Framework\DataObject;

/**
 * Interface ResolverInterface
 * @package Aheadworks\Sarp\Model\Logger\Data
 */
interface ResolverInterface
{
    /**
     * Get log entry data
     *
     * @param DataObject|ProfileInterface $object
     * @param array $additionalData
     * @return array
     */
    public function getEntryData($object, array $additionalData = []);
}
