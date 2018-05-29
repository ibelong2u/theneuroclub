<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Resolver;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\Profile\Source\Status as StatusSource;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class PaymentFailed
 * @package Aheadworks\Sarp\Model\Logger\Data\Resolver
 */
class PaymentFailed extends BaseResolver
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var StatusSource
     */
    private $statusSource;

    /**
     * @param DateTime $dateTime
     * @param StatusSource $statusSource
     */
    public function __construct(
        DateTime $dateTime,
        StatusSource $statusSource
    ) {
        $this->dateTime = $dateTime;
        $this->statusSource = $statusSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntryData($object, array $additionalData = [])
    {
        $data = $this->initEntryData($object);
        $data['title'] = 'Payment failed';

        $details = [];
        if (isset($additionalData['reattempts'])) {
            /** @var Payment[] $reattempts */
            $reattempts = $additionalData['reattempts'];
            if (count($reattempts)) {
                $payment = current($reattempts);
                if ($payment->getStatus() == Payment::STATUS_RETRYING) {
                    $details[] = sprintf(
                        'Payment cannot be processed. We\'ll retry the payment on {{formatDate %s}}',
                        $this->dateTime->formatDate($payment->getRetryAt(), false)
                    );
                } else {
                    $details[] = sprintf(
                        'Payment cannot be processed and will be skipped. Next payment will be on {{formatDate %s}}',
                        $this->dateTime->formatDate($payment->getScheduledAt(), false)
                    );
                }
            }
        }
        if (isset($additionalData['statusChanged']) && $additionalData['statusChanged']) {
            $details[] = 'Status has been changed to ' . $this->getProfileStatusTitle($object);
        }
        $data['details'] = implode(' ', $details);

        if (isset($additionalData['exception'])) {
            /** @var \Exception $exception */
            $exception = $additionalData['exception'];
            $data['error_details'] = $exception->getCode() . ': ' . $exception->getMessage();
        } else {
            $data['error_details'] = null;
        }

        return $data;
    }

    /**
     * Get profile status title
     *
     * @param ProfileInterface $profile
     * @return string
     */
    private function getProfileStatusTitle($profile)
    {
        $statusOptions = $this->statusSource->getOptions();
        $status = $profile->getStatus();
        return isset($statusOptions[$status]) ? $statusOptions[$status] : '';
    }
}
