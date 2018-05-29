<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data\Resolver;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Model\Profile\PaymentInfo;
use Aheadworks\Sarp\Model\Profile\Source\Status as StatusSource;
use Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment\ActionResult;

/**
 * Class ProfileCreated
 * @package Aheadworks\Sarp\Model\Logger\Data\Resolver
 */
class ProfileCreated extends BaseResolver
{
    /**
     * @var StatusSource
     */
    private $statusSource;

    /**
     * @param StatusSource $statusSource
     */
    public function __construct(StatusSource $statusSource)
    {
        $this->statusSource = $statusSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntryData($object, array $additionalData = [])
    {
        $data = $this->initEntryData($object);
        $data['title'] = 'Profile has been created';
        if (isset($additionalData['paymentResults'])) {
            /** @var ActionResult[] $paymentResults */
            $paymentResults = $additionalData['paymentResults'];
            $orderParts = [];
            foreach ($paymentResults as $type => $paymentResult) {
                $order = $paymentResult->getOrder();
                if ($order) {
                    $orderParts[] = sprintf(
                        'Order #{{orderLink %s %s}}%s',
                        $order->getEntityId(),
                        $order->getIncrementId(),
                        $type == PaymentInfo::PAYMENT_TYPE_INITIAL ? ' (for initial fee)' : ''
                    );
                }
            }
            if (count($paymentResults) > 1) {
                $data['details'] = implode(' and ', $orderParts) . ' have been created';
            } else {
                $data['details'] = current($orderParts) . ' has been created';
            }
        } else {
            $data['details'] = 'Status has been changed to ' . $this->getProfileStatusTitle($object);
        }
        $data['error_details'] = null;
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
