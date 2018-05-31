<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api;

/**
 * Class RequestBuilder
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api
 */
class RequestBuilder
{
    /**
     * Build request string
     *
     * @param string $method
     * @param array $data
     * @return string
     */
    public function build($method, $data)
    {
        // todo: consider add prepare method to fit sequence in AnetApi/xml/v1/schema/AnetApiSchema.xsd
        return sprintf(
            '<?xml version="1.0" encoding="utf-8"?><%s xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">%s</%s>',
            $method,
            $this->getXml(null, $data),
            $method
        );
    }

    /**
     * Get XML representation of 'fieldName' - 'fieldValue' pair
     *
     * @param string $fieldName
     * @param mixed $fieldValue
     * @return string
     */
    private function getXml($fieldName, $fieldValue)
    {
        $result = '';
        if ($fieldName) {
            $result .= sprintf('<%s>', $fieldName);
        }
        if (is_array($fieldValue)) {
            foreach ($fieldValue as $key => $value) {
                $result .= $this->getXml($key, $value);
            }
        } else {
            $result .= $this->filterValue($fieldValue);
        }
        if ($fieldName) {
            $result .= sprintf('</%s>', $fieldName);
        }
        return $result;
    }

    /**
     * Filter value
     *
     * @param string $value
     * @return string
     */
    private function filterValue($value)
    {
        return str_replace(
            ['&', '"', "'", '<', '>'],
            ['&amp;', '&quot;', '&apos;', '&lt;', '&gt;'],
            (string)$value
        );
    }
}
