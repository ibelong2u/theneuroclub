<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Response;

/**
 * Class Processor
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Response
 */
class Processor
{
    /**
     * Process raw response from api
     *
     * @param string $rawResponse
     * @return array
     */
    public function processRawResponse($rawResponse)
    {
        $rawResponse = preg_split('/^\r?$/m', $rawResponse, 2);
        $rawResponse = trim($rawResponse[1]);
        return $this->parseRawResponse($rawResponse);
    }

    /**
     * Parse raw response string
     *
     * @param string $rawResponse
     * @return array
     */
    private function parseRawResponse($rawResponse)
    {
        $initial = 0;
        $responseArray = [];

        $rawResponse = strpos($rawResponse, "\r\n\r\n") !== false
            ? substr($rawResponse, strpos($rawResponse, "\r\n\r\n") + 4)
            : $rawResponse;

        while (strlen($rawResponse)) {
            $keyPos = strpos($rawResponse, '=');
            $valuePos = strpos($rawResponse, '&')
                ? strpos($rawResponse, '&')
                : strlen($rawResponse);

            $key = substr($rawResponse, $initial, $keyPos);
            $value = substr($rawResponse, $keyPos + 1, $valuePos - $keyPos - 1);

            $responseArray[urldecode($key)] = urldecode($value);
            $rawResponse = substr($rawResponse, $valuePos + 1, strlen($rawResponse));
        }
        return $responseArray;
    }

    /**
     * Post process response data
     *
     * @param array $response
     * @return array
     */
    public function postProcessResponse($response)
    {
        foreach ($response as $key => $value) {
            $pos = strpos($key, '[');
            if ($pos !== false) {
                unset($response[$key]);
                if ($pos !== 0) {
                    $response[substr($key, 0, $pos)] = $value;
                }
            }
        }
        return $response;
    }
}
