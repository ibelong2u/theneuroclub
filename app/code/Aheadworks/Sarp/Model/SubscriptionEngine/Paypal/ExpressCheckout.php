<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\DataResolver;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\ExpressCheckout\CartValidator;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp as ApiNvp;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor as CartPersistor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Paypal\Model\Config as PaypalConfig;

/**
 * Class ExpressCheckout
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExpressCheckout
{
    /**
     * @var ApiNvp
     */
    private $api;

    /**
     * @var CartPersistor
     */
    private $cartPersistor;

    /**
     * @var SubscriptionsCartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CartValidator
     */
    private $cartValidator;

    /**
     * @var ConfigProxy
     */
    private $paypalConfigProxy;

    /**
     * @var DataResolver
     */
    private $engineDataResolver;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * ExpressCheckout constructor.
     * @param ApiNvp $api
     * @param CartPersistor $cartPersistor
     * @param SubscriptionsCartRepositoryInterface $cartRepository
     * @param CartValidator $cartValidator
     * @param ConfigProxy $paypalConfigProxy
     * @param DataResolver $engineDataResolver
     * @param Url $url
     * @param DataObjectFactory $dataObjectFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ApiNvp $api,
        CartPersistor $cartPersistor,
        SubscriptionsCartRepositoryInterface $cartRepository,
        CartValidator $cartValidator,
        ConfigProxy $paypalConfigProxy,
        DataResolver $engineDataResolver,
        Url $url,
        DataObjectFactory $dataObjectFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->api = $api;
        $this->cartPersistor = $cartPersistor;
        $this->cartRepository = $cartRepository;
        $this->cartValidator = $cartValidator;
        $this->paypalConfigProxy = $paypalConfigProxy;
        $this->engineDataResolver = $engineDataResolver;
        $this->url = $url;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Start checkout on PayPal
     *
     * @throws LocalizedException
     * @return string
     */
    public function start()
    {
        $cart = $this->cartPersistor->getSubscriptionCart();
        if (!$this->cartValidator->isValid($cart)) {
            $validationMessages = $this->cartValidator->getMessages();
            throw new LocalizedException(__(array_pop($validationMessages)));
        }

        /** @var DataObject $request */
        $request = $this->dataObjectFactory->create();
        $solutionType = $this->paypalConfigProxy->getMerchantCountry() == 'DE'
            ? PaypalConfig::EC_SOLUTION_TYPE_MARK
            : $this->paypalConfigProxy->getValue('solutionType');
        $request
            ->setCurrencyCode($cart->getCartCurrencyCode())
            ->setReturnUrl($this->url->getReturnUrl())
            ->setCancelUrl($this->url->getCancelUrl())
            ->setSolutionType($solutionType)
            ->setSuppressShipping($cart->getIsVirtual())
            ->setBillingType('RecurringPayments')
            ->setBillingAgreementDescription(
                $this->engineDataResolver->getProfileDescriptionUsingCart($cart)
            );
        $request = $this->exportAddresses($cart, $request);

        $response = $this->api->callSetExpressCheckout($request);
        return $response->getToken();
    }

    /**
     * Update subscription cart data
     *
     * @param string $token
     * @return void
     */
    public function updateCart($token)
    {
        $cart = $this->cartPersistor->getSubscriptionCart();
        /** @var DataObject $request */
        $request = $this->dataObjectFactory->create();
        $request->setToken($token);
        $this->importBillingAddress($cart, $this->api->callGetExpressCheckoutDetails($request));
        $this->cartRepository->save($cart, false);
    }

    /**
     * @param SubscriptionsCartInterface $cart
     * @param DataObject $request
     * @return DataObject
     */
    private function exportAddresses($cart, $request)
    {
        foreach ($cart->getAddresses() as $address) {
            $addressData = $this->dataObjectProcessor->buildOutputDataArray(
                $address,
                SubscriptionsCartAddressInterface::class
            );
            if (!$cart->getIsVirtual() && $address->getAddressType() == Address::TYPE_SHIPPING) {
                $request->setShippingAddress($addressData);
            } else {
                $request->setBillingAddress($addressData);
            }
        }
        return $request;
    }

    /**
     * Import billing address from response
     *
     * @param SubscriptionsCartInterface $cart
     * @param DataObject $response
     * @return SubscriptionsCartInterface
     */
    private function importBillingAddress($cart, $response)
    {
        foreach ($cart->getAddresses() as $address) {
            if ($address->getAddressType() == Address::TYPE_BILLING) {
                $address->setCustomerAddressId(null);
                $this->dataObjectHelper->populateWithArray(
                    $address,
                    $response->getBillingAddress(),
                    SubscriptionsCartAddressInterface::class
                );
            }
        }
        return $cart;
    }
}
