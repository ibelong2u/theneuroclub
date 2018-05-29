<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Request;

/**
 * Class Preprocessor
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api\Request
 */
class Preprocessor
{
    /**
     * @var Encoder
     */
    private $encoder;

    /**
     * @param Encoder $encoder
     */
    public function __construct(Encoder $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * Process request data
     *
     * @param array $data
     * @return string
     */
    public function process(array $data)
    {
        return $this->prepareKeyValuePairsStr($this->encoder->toUtf8($data));
    }

    /**
     * Prepare key-value pairs string
     *
     * @param mixed $data
     * @param string|null $prefix
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function prepareKeyValuePairsStr($data, $prefix = null)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                if ($value) {
                    if ($prefix) {
                        $key = sprintf(
                            '%s[%s]',
                            $prefix,
                            $key !== null && (!is_int($key) || is_array($value))
                            ? $key
                            : ''
                        );
                    }
                    if (is_array($value)) {
                        $preparedData = $this->prepareKeyValuePairsStr($value, $key);
                        if ($preparedData) {
                            $result[] = $preparedData;
                        }
                    } else {
                        $result[] = urlencode($key) . '=' . urlencode($value);
                    }
                }
            }
            return implode('&', $result);
        } else {
            return $data;
        }
    }
}
