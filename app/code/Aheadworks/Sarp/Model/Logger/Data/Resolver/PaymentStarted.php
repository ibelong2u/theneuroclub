<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Resolver;

/**
 * Class PaymentStarted
 * @package Aheadworks\Sarp\Model\Logger\Data\Resolver
 */
class PaymentStarted extends BaseResolver
{
    /**
     * {@inheritdoc}
     */
    public function getEntryData($object, array $additionalData = [])
    {
        $data = $this->initEntryData($object);
        $data['title'] = 'Start payment';
        $data['details'] = '';
        $data['error_details'] = null;
        return $data;
    }
}
