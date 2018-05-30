<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\PaymentInfo;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartPaymentInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class Converter
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\PaymentInfo
 */
class Converter
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Convert profile and payment instances into payment info object
     *
     * @param ProfileInterface $profile
     * @param SubscriptionsCartPaymentInterface $payment
     * @return DataObject
     */
    public function convert(ProfileInterface $profile, SubscriptionsCartPaymentInterface $payment)
    {
        $info = $this->dataObjectFactory->create($payment->getPaymentData());
        $info->setProfile(
            $this->dataObjectProcessor->buildOutputDataArray($profile, ProfileInterface::class)
        );
        foreach ($profile->getAddresses() as $address) {
            $addressData = $this->dataObjectProcessor->buildOutputDataArray(
                $address,
                ProfileAddressInterface::class
            );
            if ($address->getAddressType() == Address::TYPE_BILLING) {
                $info->setBillingAddress($addressData);
            } elseif ($address->getAddressType() == Address::TYPE_SHIPPING && !$profile->getIsCartVirtual()) {
                $info->setShippingddress($addressData);
            }
        }
        return $info;
    }
}
