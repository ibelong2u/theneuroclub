<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Address;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Converter\CustomerAddressConverter;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Converter\QuoteAddressConverter;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Class ConverterManager
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Address
 */
class ConverterManager
{
    /**
     * @var QuoteAddressConverter
     */
    private $toQuoteAddressConverter;

    /**
     * @var CustomerAddressConverter
     */
    private $customerAddressConverter;

    /**
     * @param QuoteAddressConverter $toQuoteAddressConverter
     * @param CustomerAddressConverter $customerAddressConverter
     */
    public function __construct(
        QuoteAddressConverter $toQuoteAddressConverter,
        CustomerAddressConverter $customerAddressConverter
    ) {
        $this->toQuoteAddressConverter = $toQuoteAddressConverter;
        $this->customerAddressConverter = $customerAddressConverter;
    }

    /**
     * Convert into quote address instance
     *
     * @param SubscriptionsCartAddressInterface $address
     * @return QuoteAddress
     */
    public function toQuoteAddress(SubscriptionsCartAddressInterface $address)
    {
        return $this->toQuoteAddressConverter->convert($address);
    }

    /**
     * Convert into customer address instance
     *
     * @param SubscriptionsCartAddressInterface $address
     * @return CustomerAddress
     */
    public function toCustomerAddress(SubscriptionsCartAddressInterface $address)
    {
        return $this->customerAddressConverter->toCustomerAddress($address);
    }

    /**
     * Convert customer address into subscription cart address instance
     *
     * @param CustomerAddress $customerAddress
     * @return SubscriptionsCartAddressInterface
     */
    public function fromCustomerAddress(CustomerAddress $customerAddress)
    {
        return $this->customerAddressConverter->fromCustomerAddress($customerAddress);
    }
}
