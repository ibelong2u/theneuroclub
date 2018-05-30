<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\LocalizedExceptionFactory;

/**
 * Class ErrorHandler
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe\Api
 */
class ErrorHandler
{
    /**
     * Error types
     */
    const ERROR_TYPE_API_CONNECTION_ERROR = 'api_connection_error';
    const ERROR_TYPE_API_ERROR = 'api_error';
    const ERROR_TYPE_AUTHENTICATION_ERROR = 'authentication_error';
    const ERROR_TYPE_CARD_ERROR = 'card_error';
    const ERROR_TYPE_INVALID_REQUEST_ERROR = 'invalid_request_error';
    const ERROR_TYPE_RATE_LIMIT_ERROR = 'rate_limit_error';

    /**
     * @var LocalizedExceptionFactory
     */
    private $exceptionFactory;

    /**
     * @param LocalizedExceptionFactory $exceptionFactory
     */
    public function __construct(LocalizedExceptionFactory $exceptionFactory)
    {
        $this->exceptionFactory = $exceptionFactory;
    }

    /**
     * Handle request errors
     *
     * @param string $code
     * @param array $response
     * @throws LocalizedException
     * @return void
     */
    public function handle($code, $response)
    {
        $errorText = __('Please try another payment method or contact us so we can assist you.');

        $error = $response['error'];
        $errorType = $error['type'];
        $errorMessage = isset($error['message']) ? $error['message'] : $errorText;
        if ($code == 400 && $errorType == self::ERROR_TYPE_INVALID_REQUEST_ERROR
            || $code == 402 && $errorType == self::ERROR_TYPE_CARD_ERROR
        ) {
            $errorText = $errorMessage;
        }

        /** @var LocalizedException $exception */
        $exception = $this->exceptionFactory->create(
            ['phrase' => __('Gateway has rejected request. %1', $errorText)]
        );
        throw $exception;
    }
}
