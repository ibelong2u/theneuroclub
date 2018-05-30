<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Plugin;

use Adyen\Payment\Gateway\Validator\GeneralResponseValidator;
use Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Observer\CcDataAssignObserver;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Exception\PaymentActionException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Class GeneralResponseValidatorPlugin
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Adyen\Plugin
 */
class GeneralResponseValidatorPlugin
{
    /**
     * @param GeneralResponseValidator $subject
     * @param \Closure $proceed
     * @param array $validationSubject
     * @return ResultInterface
     * @throws PaymentActionException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate(GeneralResponseValidator $subject, \Closure $proceed, array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $paymentDataObjectInterface = SubjectReader::readPayment($validationSubject);
        $payment = $paymentDataObjectInterface->getPayment();
        $isRecurringPayment = $payment->getAdditionalInformation(CcDataAssignObserver::IS_AW_SARP_RECURRING_PAYMENT);

        if ($isRecurringPayment
            && $response
            && isset($response['resultCode'])
            && $response['resultCode'] == 'Refused'
        ) {
            if ($response['refusalReason']) {
                $refusalReason = $response['refusalReason'];
                switch ($refusalReason) {
                    case 'Transaction Not Permitted':
                        $errorMessage = 'The transaction is not permitted.';
                        break;
                    case 'CVC Declined':
                        $errorMessage = 'Declined due to the Card Security Code(CVC) being incorrect. '
                            . 'Please check your CVC code!';
                        break;
                    case 'Restricted Card':
                        $errorMessage = 'The card is restricted.';
                        break;
                    case '803 PaymentDetail not found':
                        $errorMessage = 'The payment is REFUSED because the saved card is removed. '
                            . 'Please try an other payment method.';
                        break;
                    case 'Expiry month not set':
                        $errorMessage = 'The expiry month is not set. Please check your expiry month!';
                        break;
                    default:
                        $errorMessage = 'The payment is REFUSED.';
                        break;
                }
            } else {
                $errorMessage = 'The payment is REFUSED.';
            }
            throw new PaymentActionException(__($errorMessage));
        }
        return $proceed($validationSubject);
    }
}
