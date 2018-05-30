<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Adminhtml\Subscription\Info;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Model\PaymentMethodList;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Resolver\FullName as FullNameResolver;
use Magento\Backend\Block\Template\Context;
use Magento\Directory\Model\CountryFactory;

/**
 * Class Addresses
 * @package Aheadworks\Sarp\Block\Adminhtml\Subscription\Info
 */
class Addresses extends \Magento\Backend\Block\Template
{
    /**
     * @var FullNameResolver
     */
    private $fullNameResolver;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var PaymentMethodList
     */
    private $paymentMethodList;

    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp::subscription/info/addresses.phtml';

    /**
     * @param Context $context
     * @param FullNameResolver $fullNameResolver
     * @param CountryFactory $countryFactory
     * @param PaymentMethodList $paymentMethodList
     * @param array $data
     */
    public function __construct(
        Context $context,
        FullNameResolver $fullNameResolver,
        CountryFactory $countryFactory,
        PaymentMethodList $paymentMethodList,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->fullNameResolver = $fullNameResolver;
        $this->countryFactory = $countryFactory;
        $this->paymentMethodList = $paymentMethodList;
    }

    /**
     * Get profile entity
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set profile entity
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * Get shipping address
     *
     * @return ProfileAddressInterface|null
     */
    public function getShippingAddress()
    {
        if (!$this->getProfile()->getIsCartVirtual()) {
            foreach ($this->getProfile()->getAddresses() as $address) {
                if ($address->getAddressType() == Address::TYPE_SHIPPING) {
                    return $address;
                }
            }
        }
        return null;
    }

    /**
     * Get billing address
     *
     * @return ProfileAddressInterface|null
     */
    public function getBillingAddress()
    {
        foreach ($this->getProfile()->getAddresses() as $address) {
            if ($address->getAddressType() == Address::TYPE_BILLING) {
                return $address;
            }
        }
        return null;
    }

    /**
     * Get full name
     *
     * @param ProfileAddressInterface $address
     * @return string
     */
    public function getFullName($address)
    {
        return $this->fullNameResolver->getFullName($address);
    }

    /**
     * Get country name
     *
     * @param string $countryId
     * @return string
     */
    public function getCountryName($countryId)
    {
        $country = $this->countryFactory->create()->loadByCode($countryId);
        return $country->getName();
    }

    /**
     * Get payment method title
     *
     * @return string
     */
    public function getPaymentMethodTitle()
    {
        $profile = $this->getProfile();
        if ($profile->getPaymentMethodTitle()) {
            return $profile->getPaymentMethodTitle();
        } else {
            $method = $this->paymentMethodList
                ->getMethod($profile->getEngineCode(), $profile->getPaymentMethodCode());
            return $method ? $method->getTitle() : '';
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getProfile()) {
            return '';
        }
        return parent::_toHtml();
    }
}
