<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Magento\Framework\DataObject;

/**
 * Interface LoggerInterface
 * @package Aheadworks\Sarp\Model\Logger
 */
interface LoggerInterface
{
    /**
     * Profile created entry type
     */
    const ENTRY_TYPE_PROFILE_CREATED_SUCCESSFUL = 'profile_created';

    /**
     * Profile creation failed entry type
     */
    const ENTRY_TYPE_PROFILE_CREATION_FAILED = 'profile_creation_failed';

    /**
     * Profile status changed entry type
     */
    const ENTRY_TYPE_PROFILE_STATUS_CHANGED = 'profile_status_changed';

    /**
     * Payment started entry type
     */
    const ENTRY_TYPE_PAYMENT_STARTED = 'payment_started';

    /**
     * Payment authorized entry type
     */
    const ENTRY_TYPE_PAYMENT_AUTHORIZED = 'payment_authorized';

    /**
     * Payment captured entry type
     */
    const ENTRY_TYPE_PAYMENT_CAPTURED = 'payment_captured';

    /**
     * Payment paid entry type
     */
    const ENTRY_TYPE_PAYMENT_PAID = 'payment_paid';

    /**
     * Payment fail entry type
     */
    const ENTRY_TYPE_PAYMENT_FAIL = 'payment_fail';

    /**
     * Log notice
     *
     * @param DataObject|ProfileInterface $object
     * @param string $entryType
     * @param array $additionalInfo
     * @return void
     */
    public function notice($object, $entryType, array $additionalInfo = []);

    /**
     * Log warning
     *
     * @param DataObject|ProfileInterface $object
     * @param string $entryType
     * @param array $additionalInfo
     * @return void
     */
    public function warning($object, $entryType, array $additionalInfo = []);

    /**
     * Log error
     *
     * @param DataObject|ProfileInterface $object
     * @param string $entryType
     * @param array $additionalInfo
     * @return void
     */
    public function error($object, $entryType, array $additionalInfo = []);
}
