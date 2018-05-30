<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\LocalizedExceptionFactory;

/**
 * Class ErrorHandler
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api
 */
class ErrorHandler
{
    /**
     * @var array
     */
    private $unProcessableErrorCodes = ['E00001', 'E00012', 'E00013'];

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
     * Handle call errors
     *
     * @param array $response
     * @throws LocalizedException
     * @return void
     */
    public function handle($response)
    {
        $errorCode = $response['messages']['message']['code'];
        if (in_array($errorCode, $this->unProcessableErrorCodes)) {
            $errorText = $response['messages']['message']['text'];
        } else {
            $errorText = __('Please try another payment method or contact us so we can assist you.');
        }
        /** @var LocalizedException $exception */
        $exception = $this->exceptionFactory->create(
            ['phrase' => __('Gateway has rejected request. %1', $errorText)]
        );
        throw $exception;
    }
}
