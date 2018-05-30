<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Converter;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use \Aheadworks\Sarp\Model\SubscriptionsCart\Address\Converter\CustomerAddressConverter;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;

/**
 * Class TaxQuoteDetailsConverter
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxQuoteDetailsConverter
{
    /**
     * @var QuoteDetailsInterfaceFactory
     */
    private $taxQuoteDetailsFactory;

    /**
     * @var TaxClassKeyInterfaceFactory
     */
    private $taxClassKeyFactory;

    /**
     * @var CustomerAddressConverter
     */
    private $customerAddressConverter;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @param QuoteDetailsInterfaceFactory $taxQuoteDetailsFactory
     * @param TaxClassKeyInterfaceFactory $taxClassKeyFactory
     * @param CustomerAddressConverter $customerAddressConverter
     * @param CustomerRepositoryInterface $customerRepository
     * @param GroupRepositoryInterface $customerGroupRepository
     */
    public function __construct(
        QuoteDetailsInterfaceFactory $taxQuoteDetailsFactory,
        TaxClassKeyInterfaceFactory $taxClassKeyFactory,
        CustomerAddressConverter $customerAddressConverter,
        CustomerRepositoryInterface $customerRepository,
        GroupRepositoryInterface $customerGroupRepository
    ) {
        $this->taxQuoteDetailsFactory = $taxQuoteDetailsFactory;
        $this->taxClassKeyFactory = $taxClassKeyFactory;
        $this->customerAddressConverter = $customerAddressConverter;
        $this->customerRepository = $customerRepository;
        $this->customerGroupRepository = $customerGroupRepository;
    }

    /**
     * Convert into tax quote details instance
     *
     * @param SubscriptionsCartInterface $cart
     * @param QuoteDetailsItemInterface[] $taxQuoteDetailsItems
     * @return QuoteDetailsInterface
     */
    public function convert(SubscriptionsCartInterface $cart, array $taxQuoteDetailsItems)
    {
        /** @var QuoteDetailsInterface $taxQuoteDetails */
        $taxQuoteDetails = $this->taxQuoteDetailsFactory->create();
        if (count($taxQuoteDetailsItems)) {
            foreach ($cart->getAddresses() as $address) {
                if ($address->getAddressType() == Address::TYPE_BILLING) {
                    $taxQuoteDetails->setBillingAddress(
                        $this->customerAddressConverter->toCustomerAddress($address)
                    );
                } else {
                    $taxQuoteDetails->setShippingAddress(
                        $this->customerAddressConverter->toCustomerAddress($address)
                    );
                }
            }

            $customerGroupId = $cart->getCustomerId()
                ? $this->customerRepository->getById($cart->getCustomerId())->getGroupId()
                : GroupInterface::NOT_LOGGED_IN_ID;
            $customerTaxClassId = $this->customerGroupRepository->getById($customerGroupId)
                ->getTaxClassId();
            $taxQuoteDetails
                ->setCustomerTaxClassKey(
                    $this->taxClassKeyFactory->create()
                        ->setType(TaxClassKeyInterface::TYPE_ID)
                        ->setValue($customerTaxClassId)
                )
                ->setItems($taxQuoteDetailsItems)
                ->setCustomerId($cart->getCustomerId());
        }
        return $taxQuoteDetails;
    }
}
