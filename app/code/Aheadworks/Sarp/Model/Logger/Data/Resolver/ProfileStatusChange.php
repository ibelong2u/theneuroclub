<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Resolver;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\Profile\Source\Status as StatusSource;

/**
 * Class ProfileStatusChange
 * @package Aheadworks\Sarp\Model\Logger\Data\Resolver
 */
class ProfileStatusChange extends BaseResolver
{
    /**
     * @var StatusSource
     */
    private $statusSource;

    /**
     * @param StatusSource $statusSource
     */
    public function __construct(StatusSource $statusSource)
    {
        $this->statusSource = $statusSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntryData($object, array $additionalData = [])
    {
        $data = $this->initEntryData($object);
        $data['title'] = 'Profile has been updated';
        $data['details'] = 'Status has been changed to ' . $this->getProfileStatusTitle($object);
        $data['error_details'] = null;
        return $data;
    }

    /**
     * Get profile status title
     *
     * @param ProfileInterface $profile
     * @return string
     */
    private function getProfileStatusTitle($profile)
    {
        $statusOptions = $this->statusSource->getOptions();
        $status = $profile->getStatus();
        return isset($statusOptions[$status]) ? $statusOptions[$status] : '';
    }
}
