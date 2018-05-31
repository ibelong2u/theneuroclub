<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Customer\Subscription\Info;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Block\Customer\Subscription\Info\Address\Form as AddressForm;
use Aheadworks\Sarp\Model\PaymentMethodList;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Resolver\FullName as FullNameResolver;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Directory\Model\CountryFactory;

/**
 * Class Addresses
 * @package Aheadworks\Sarp\Block\Customer\Subscription\Info
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Addresses extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var Session
     */
    private $customerSession;

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
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param Session $customerSession
     * @param FullNameResolver $fullNameResolver
     * @param CountryFactory $countryFactory
     * @param PaymentMethodList $paymentMethodList
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        Session $customerSession,
        FullNameResolver $fullNameResolver,
        CountryFactory $countryFactory,
        PaymentMethodList $paymentMethodList,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileRepository = $profileRepository;
        $this->customerSession = $customerSession;
        $this->fullNameResolver = $fullNameResolver;
        $this->countryFactory = $countryFactory;
        $this->paymentMethodList = $paymentMethodList;
    }

    /**
     * Get profile ID
     *
     * @return int|null
     */
    private function getProfileId()
    {
        return $this->getRequest()->getParam('profile_id');
    }

    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profileRepository->get($this->getProfileId());
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
     * Get edit address form html
     *
     * @param ProfileAddressInterface $address
     * @return string
     * @throws LocalizedException
     */
    public function getEditAddressFormHtml($address)
    {
        /** @var AddressForm $block */
        $block = $this->getLayout()->createBlock(
            AddressForm::class,
            '',
            ['data' => ['address' => $address]]
        );
        return $block->toHtml();
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
     * Get payment method name
     *
     * @return string
     */
    public function getPaymentMethodName()
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
        if (!$this->getProfileId() || !$this->customerSession->isLoggedIn()) {
            return '';
        }
        return parent::_toHtml();
    }
}
