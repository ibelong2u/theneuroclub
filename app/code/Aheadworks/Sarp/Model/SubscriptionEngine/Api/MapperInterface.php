<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Api;

/**
 * Interface MapperInterface
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Api
 */
interface MapperInterface
{
    /**
     * todo: consider rename $method to $resource (or more suitable),
     *       because $method is not suitable for REST resources
     * Map request data to api
     *
     * @param string $method
     * @param array $data
     * @return array
     */
    public function toApi($method, $data);

    /**
     * todo: consider rename $method to $resource (or more suitable),
     *       because $method is not suitable for REST resources
     * Map response data from api
     *
     * @param string $method
     * @param array $data
     * @return array
     */
    public function fromApi($method, $data);
}
