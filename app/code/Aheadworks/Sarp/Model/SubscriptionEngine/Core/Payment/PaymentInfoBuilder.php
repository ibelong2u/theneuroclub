<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterface;
use Aheadworks\Sarp\Api\Data\ProfilePaymentInfoInterfaceFactory;
use Aheadworks\Sarp\Model\Profile\PaymentInfo;

/**
 * Class PaymentInfoBuilder
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment
 */
class PaymentInfoBuilder
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var ProfilePaymentInfoInterfaceFactory
     */
    private $factory;

    /**
     * @param ProfilePaymentInfoInterfaceFactory $factory
     */
    public function __construct(ProfilePaymentInfoInterfaceFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Set profile
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile(ProfileInterface $profile)
    {
        $this->data['profile'] = $profile;
        return $this;
    }

    /**
     * Set payment type
     *
     * @param string $paymentType
     * @return $this
     */
    public function setPaymentType($paymentType)
    {
        $this->data['payment_type'] = $paymentType;
        return $this;
    }

    /**
     * Reset state
     *
     * @return void
     */
    private function resetState()
    {
        $this->data = [];
    }

    /**
     * Check if state is valid for build
     *
     * @return bool
     */
    private function isStateValidForBuild()
    {
        $requiredKeys = ['profile', 'payment_type'];
        foreach ($requiredKeys as $key) {
            if (!isset($this->data[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Build payment info instance
     *
     * @return ProfilePaymentInfoInterface
     */
    public function build()
    {
        /** @var ProfilePaymentInfoInterface $instance */
        $instance = $this->factory->create();
        if ($this->isStateValidForBuild()) {
            $this->initInstance($instance);
        }
        $this->resetState();
        return $instance;
    }

    /**
     * Init payment info instance
     *
     * @param ProfilePaymentInfoInterface $instance
     * @return void
     */
    private function initInstance(ProfilePaymentInfoInterface $instance)
    {
        /** @var ProfileInterface $profile */
        $profile = $this->data['profile'];
        $paymentType = $this->data['payment_type'];

        $instance
            ->setPaymentType($paymentType)
            ->setCurrencyCode($profile->getProfileCurrencyCode())
            ->setBaseCurrencyCode($profile->getBaseCurrencyCode());

        switch ($paymentType) {
            case PaymentInfo::PAYMENT_TYPE_INITIAL:
                $instance
                    ->setAmount($profile->getInitialFee())
                    ->setBaseAmount($profile->getBaseInitialFee())
                    ->setShippingAmount(0)
                    ->setBaseShippingAmount(0)
                    ->setTaxAmount(0)
                    ->setBaseTaxAmount(0)
                    ->setGrandTotal($profile->getInitialFee())
                    ->setBaseGrandTotal($profile->getBaseInitialFee());
                break;
            case PaymentInfo::PAYMENT_TYPE_TRIAL:
                $trialGrandTotal = $profile->getTrialSubtotal() + $profile->getShippingAmount()
                    + $profile->getTrialTaxAmount();
                $baseTrialGrandTotal = $profile->getBaseTrialSubtotal() + $profile->getBaseShippingAmount()
                    + $profile->getBaseTrialTaxAmount();
                $instance
                    ->setAmount($profile->getTrialSubtotal())
                    ->setBaseAmount($profile->getBaseTrialSubtotal())
                    ->setShippingAmount($profile->getShippingAmount())
                    ->setBaseShippingAmount($profile->getBaseShippingAmount())
                    ->setTaxAmount($profile->getTrialTaxAmount())
                    ->setBaseTaxAmount($profile->getBaseTrialTaxAmount())
                    ->setGrandTotal($trialGrandTotal)
                    ->setBaseGrandTotal($baseTrialGrandTotal);
                break;
            case PaymentInfo::PAYMENT_TYPE_REGULAR:
                $instance
                    ->setAmount($profile->getSubtotal())
                    ->setBaseAmount($profile->getBaseSubtotal())
                    ->setShippingAmount($profile->getShippingAmount())
                    ->setBaseShippingAmount($profile->getBaseShippingAmount())
                    ->setTaxAmount($profile->getTaxAmount())
                    ->setBaseTaxAmount($profile->getBaseTaxAmount())
                    ->setGrandTotal($profile->getGrandTotal())
                    ->setBaseGrandTotal($profile->getBaseGrandTotal());
                break;
            default:
                break;
        }
    }
}
