<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Request;

/**
 * Class Encoder
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Request
 */
class Encoder
{
    /**
     * Encode array to UTF-8
     *
     * @param array $data
     * @return array
     */
    public function toUtf8(array $data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = $this->performEncodeToUtf8($value);
        }
        return $result;
    }

    /**
     * Perform encode to UTF-8
     *
     * @param mixed $value
     * @return array|string
     */
    private function performEncodeToUtf8($value)
    {
        $result = $value;
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                $result[$key] = $this->performEncodeToUtf8($item);
            }
        } elseif (is_string($value) && mb_detect_encoding($value, 'UTF-8', true) != 'UTF-8') {
            $result = utf8_encode($value);
        }
        return $result;
    }
}
