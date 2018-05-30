<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Error;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\LocalizedExceptionFactory;
use Magento\Paypal\Model\Api\ProcessableException;
use Magento\Paypal\Model\Api\ProcessableExceptionFactory;

/**
 * Class Handler
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Error
 */
class Handler
{
    /**
     * Api processable errors
     *
     * @var array
     */
    private $processableErrors = [
        ProcessableException::API_INTERNAL_ERROR,
        ProcessableException::API_UNABLE_PROCESS_PAYMENT_ERROR_CODE,
        ProcessableException::API_DO_EXPRESS_CHECKOUT_FAIL,
        ProcessableException::API_UNABLE_TRANSACTION_COMPLETE,
        ProcessableException::API_TRANSACTION_EXPIRED,
        ProcessableException::API_MAX_PAYMENT_ATTEMPTS_EXCEEDED,
        ProcessableException::API_COUNTRY_FILTER_DECLINE,
        ProcessableException::API_MAXIMUM_AMOUNT_FILTER_DECLINE,
        ProcessableException::API_OTHER_FILTER_DECLINE,
        ProcessableException::API_ADDRESS_MATCH_FAIL
    ];

    /**
     * @var ProcessableExceptionFactory
     */
    private $processableExceptionFactory;

    /**
     * @var LocalizedExceptionFactory
     */
    private $localizedExceptionFactory;

    /**
     * @param ProcessableExceptionFactory $processableExceptionFactory
     * @param LocalizedExceptionFactory $localizedExceptionFactory
     */
    public function __construct(
        ProcessableExceptionFactory $processableExceptionFactory,
        LocalizedExceptionFactory $localizedExceptionFactory
    ) {
        $this->processableExceptionFactory = $processableExceptionFactory;
        $this->localizedExceptionFactory = $localizedExceptionFactory;
    }

    /**
     * Handle call errors
     *
     * @param array $response
     * @throws LocalizedException
     * @return void
     */
    public function handleCallErrors(array $response)
    {
        $errors = $this->getErrors($response);
        if ($errors) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error['message'];
            }
            $errorMessages = implode(' ', $errorMessages);
            $exceptionPhrase = __('PayPal gateway has rejected request. %1', $errorMessages);

            /** @var LocalizedException $exception */
            $firstError = $errors[0]['code'];
            $exception = in_array($firstError, $this->processableErrors)
                ? $this->processableExceptionFactory->create(
                    ['phrase' => $exceptionPhrase, 'code' => $firstError]
                )
                : $this->localizedExceptionFactory->create(
                    ['phrase' => $exceptionPhrase]
                );

            throw $exception;
        }
    }

    /**
     * Get errors from response
     *
     * @param array $response
     * @return array
     */
    private function getErrors(array $response)
    {
        $errors = [];

        for ($i = 0; isset($response['L_ERRORCODE' . $i]); $i++) {
            $errorCode = $response['L_ERRORCODE' . $i];
            $errorMessage = $this->formatErrorMessage(
                $errorCode,
                $response['L_SHORTMESSAGE' . $i],
                isset($response['L_LONGMESSAGE' . $i]) ? $response['L_LONGMESSAGE' . $i] : null
            );
            $errors[] = [
                'code' => $errorCode,
                'message' => $errorMessage,
            ];
        }

        return $errors;
    }

    /**
     * Format error message
     *
     * @param string $errorCode
     * @param string $shortErrorMessage
     * @param string $longErrorMessage
     * @return string
     */
    private function formatErrorMessage($errorCode, $shortErrorMessage, $longErrorMessage)
    {
        $longErrorMessage  = preg_replace('/\.$/', '', $longErrorMessage);
        $shortErrorMessage = preg_replace('/\.$/', '', $shortErrorMessage);

        return $longErrorMessage
            ? sprintf('%s (#%s: %s).', $longErrorMessage, $errorCode, $shortErrorMessage)
            : sprintf('#%s: %s.', $errorCode, $shortErrorMessage);
    }
}
