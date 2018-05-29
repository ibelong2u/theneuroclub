<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Address\Converter;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Converter\QuoteConverter;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\AddressFactory as QuoteAddressFactory;
use Magento\Framework\DataObject\Copy;

/**
 * Class QuoteAddressConverter
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Address\Converter
 */
class QuoteAddressConverter
{
    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var QuoteConverter
     */
    private $toQuoteConverter;

    /**
     * @var SubscriptionsCartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param QuoteAddressFactory $quoteAddressFactory
     * @param QuoteConverter $toQuoteConverter
     * @param SubscriptionsCartRepositoryInterface $cartRepository
     * @param Copy $objectCopyService
     */
    public function __construct(
        QuoteAddressFactory $quoteAddressFactory,
        QuoteConverter $toQuoteConverter,
        SubscriptionsCartRepositoryInterface $cartRepository,
        Copy $objectCopyService
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->toQuoteConverter = $toQuoteConverter;
        $this->cartRepository = $cartRepository;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Convert subscription cart address into quote address instance
     *
     * @param SubscriptionsCartAddressInterface $address
     * @return QuoteAddress
     */
    public function convert(SubscriptionsCartAddressInterface $address)
    {
        /** @var QuoteAddress $quoteAddress */
        $quoteAddress = $this->quoteAddressFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp_cart_address',
            'to_quote_address',
            $address,
            $quoteAddress
        );
        if ($address->getCartId()) {
            $subscriptionCart = $this->cartRepository->getActive($address->getCartId());
            $quote = $this->toQuoteConverter->convert($subscriptionCart);
            $quoteAddress->setQuote($quote);
        }

        return $quoteAddress;
    }
}
