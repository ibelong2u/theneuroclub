<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Resolver;

/**
 * Class ProfileCreationFailed
 * @package Aheadworks\Sarp\Model\Logger\Data\Resolver
 */
class ProfileCreationFailed extends BaseResolver
{
    /**
     * {@inheritdoc}
     */
    public function getEntryData($object, array $additionalData = [])
    {
        $data = $this->initEntryData($object);
        $data['title'] = 'Profile cannot be created';
        $data['details'] = 'Status hasn\'t been changed';
        if (isset($additionalData['exception'])) {
            /** @var \Exception $exception */
            $exception = $additionalData['exception'];
            $data['error_details'] = $exception->getCode() . ': ' . $exception->getMessage();
        } else {
            $data['error_details'] = null;
        }
        return $data;
    }
}
