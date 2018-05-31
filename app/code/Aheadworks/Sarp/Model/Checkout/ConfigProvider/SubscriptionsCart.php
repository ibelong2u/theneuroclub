<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Checkout\ConfigProvider;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartTotalsInterfaceFactory as TotalsFactory;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Model\Checkout\ConfigProviderInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Persistor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\DateChecker;

/**
 * Class SubscriptionsCart
 * @package Aheadworks\Sarp\Model\Checkout\ConfigProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriptionsCart implements ConfigProviderInterface
{
    /**
     * @var Persistor
     */
    private $cartPersistor;

    /**
     * @var SubscriptionsCartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var DateChecker
     */
    private $dateChecker;

    /**
     * @param Persistor $cartPersistor
     * @param SubscriptionsCartRepositoryInterface $cartRepository
     * @param StoreManagerInterface $storeManager
     * @param FormatInterface $localeFormat
     * @param TimezoneInterface $localeDate
     * @param UrlInterface $url
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param FormKey $formKey
     * @param DateChecker $dateChecker
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Persistor $cartPersistor,
        SubscriptionsCartRepositoryInterface $cartRepository,
        StoreManagerInterface $storeManager,
        FormatInterface $localeFormat,
        TimezoneInterface $localeDate,
        UrlInterface $url,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        FormKey $formKey,
        DateChecker $dateChecker
    ) {
        $this->cartPersistor = $cartPersistor;
        $this->cartRepository = $cartRepository;
        $this->storeManager = $storeManager;
        $this->localeFormat = $localeFormat;
        $this->localeDate = $localeDate;
        $this->url = $url;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->formKey = $formKey;
        $this->dateChecker = $dateChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'formKey' => $this->formKey->getFormKey(),
            'subscriptionsCart' => $this->getSubscriptionsCartData(),
            'storeCode' => $this->getStoreCode(),
            'priceFormat' => $this->getPriceFormat(),
            'basePriceFormat' => $this->getBasePriceFormat(),
            'dateFormat' => $this->localeDate->getDateFormat(\IntlDateFormatter::SHORT),
            'cartUrl' => $this->url->getUrl('aw_sarp/cart/index'),
            'checkoutUrl' => $this->url->getUrl('aw_sarp/checkout/index'),
            'currentDate' => $this->dateChecker->getCurrentDateFrontend(),
            'defaultSuccessPageUrl' => $this->url->getUrl('aw_sarp/checkout/success')
        ];
    }

    /**
     * Get subscriptions cart data
     *
     * @return array
     */
    private function getSubscriptionsCartData()
    {
        $data = [];
        $cartId = $this->cartPersistor->getSubscriptionCart()->getCartId();
        if ($cartId) {
            $cart = $this->cartRepository->getActive($cartId);
            $data = $this->dataObjectProcessor->buildOutputDataArray(
                $cart,
                SubscriptionsCartInterface::class
            );
        }

        return $data;
    }

    /**
     * Get store code
     *
     * @return string
     */
    private function getStoreCode()
    {
        $cart = $this->cartPersistor->getSubscriptionCart();
        return $this->storeManager->getStore($cart->getStoreId())->getCode();
    }

    /**
     * Get price format
     *
     * @return array
     */
    private function getPriceFormat()
    {
        return $this->localeFormat->getPriceFormat(
            null,
            $this->cartPersistor->getSubscriptionCart()->getCartCurrencyCode()
        );
    }

    /**
     * Get base price format
     *
     * @return array
     */
    private function getBasePriceFormat()
    {
        return $this->localeFormat->getPriceFormat(
            null,
            $this->cartPersistor->getSubscriptionCart()->getBaseCurrencyCode()
        );
    }
}
