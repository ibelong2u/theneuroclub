<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Shipping;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Api\Data\SubscriptionsCartInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\ItemsRegistry;
use Aheadworks\Sarp\Model\SubscriptionsCart\Item\ConverterManager as ItemConverterManager;
use Magento\Quote\Model\Quote\Address\Item as QuoteAddressItem;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Model\CarrierFactoryInterface;
use Magento\Shipping\Model\Rate\Result as RateResult;
use Magento\Shipping\Model\Rate\ResultFactory as RateResultFactory;
use Magento\Shipping\Model\Shipping;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class RatesCollector
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Shipping
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RatesCollector
{
    /**
     * @var CarrierFactoryInterface
     */
    private $carrierFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var RateRequestFactory
     */
    private $rateRequestFactory;

    /**
     * @var RateResultFactory
     */
    private $rateResultFactory;

    /**
     * @var SubscriptionsCartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ItemsRegistry
     */
    private $itemsRegistry;

    /**
     * @var ItemConverterManager
     */
    private $itemsConverterManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Shipping
     */
    private $shipping;

    /**
     * @param CarrierFactoryInterface $carrierFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ObjectFactory $objectFactory
     * @param RateRequestFactory $rateRequestFactory
     * @param RateResultFactory $rateResultFactory
     * @param SubscriptionsCartRepositoryInterface $cartRepository
     * @param ItemsRegistry $itemsRegistry
     * @param ItemConverterManager $itemsConverterManager
     * @param StoreManagerInterface $storeManager
     * @param Shipping $shipping
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CarrierFactoryInterface $carrierFactory,
        ScopeConfigInterface $scopeConfig,
        ObjectFactory $objectFactory,
        RateRequestFactory $rateRequestFactory,
        RateResultFactory $rateResultFactory,
        SubscriptionsCartRepositoryInterface $cartRepository,
        ItemsRegistry $itemsRegistry,
        ItemConverterManager $itemsConverterManager,
        StoreManagerInterface $storeManager,
        Shipping $shipping
    ) {
        $this->carrierFactory = $carrierFactory;
        $this->scopeConfig = $scopeConfig;
        $this->objectFactory = $objectFactory;
        $this->rateRequestFactory = $rateRequestFactory;
        $this->rateResultFactory = $rateResultFactory;
        $this->cartRepository = $cartRepository;
        $this->itemsRegistry = $itemsRegistry;
        $this->itemsConverterManager = $itemsConverterManager;
        $this->storeManager = $storeManager;
        $this->shipping = $shipping;
    }

    /**
     * Collect shipping rates
     *
     * @param SubscriptionsCartAddressInterface $address
     * @param SubscriptionsCartInterface $cart
     * @return RateResult
     */
    public function collect(SubscriptionsCartAddressInterface $address, SubscriptionsCartInterface $cart)
    {
        $rateRequest = $this->initRequest($address, $cart);
        /** @var RateResult $rateResult */
        $rateResult = $this->rateResultFactory->create();

        $carriers = $this->scopeConfig->getValue('carriers', ScopeInterface::SCOPE_STORE);
        foreach (array_keys($carriers) as $carrierCode) {
            $result = $this->collectCarrierRates($carrierCode, $rateRequest);
            if ($result) {
                $rateResult->append($result);
            }
        }

        return $rateResult;
    }

    /**
     * Init request object
     *
     * @param SubscriptionsCartAddressInterface $address
     * @param SubscriptionsCartInterface $cart
     * @return RateRequest
     */
    private function initRequest(
        SubscriptionsCartAddressInterface $address,
        SubscriptionsCartInterface $cart
    ) {
        /** @var \Magento\Store\Api\Data\StoreInterface|\Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();
        $street = $address->getStreet() ? implode('\n', $address->getStreet()) : '';
        $requestData = [
            'store_id' => $cart->getStoreId(),
            'website_id' => $store->getWebsiteId(),
            'all_items' => $this->getQuoteAddressItems($address, $cart),
            'package_value' => $cart->getBaseSubtotal(),
            'package_value_with_discount' => $cart->getBaseSubtotal(),
            'package_physical_value' => $cart->getBaseSubtotal(),
            'package_qty' => $address->getQty(),
            'package_weight' => $address->getWeight(),
            'base_currency' => $store->getBaseCurrency(),
            'package_currency' => $store->getCurrentCurrency(),
            'country_id' => $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE
            ),
            'region_id' => $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_REGION_ID,
                ScopeInterface::SCOPE_STORE
            ),
            'city' => $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_CITY,
                ScopeInterface::SCOPE_STORE
            ),
            'postcode' => $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_ZIP,
                ScopeInterface::SCOPE_STORE
            ),
            'dest_country_id' => $address->getCountryId(),
            'dest_region_id' => $address->getRegionId(),
            'dest_region_code' => $address->getRegion(),
            'dest_street' => $street,
            'dest_city' => $address->getCity(),
            'dest_postcode' => $address->getPostcode()
        ];

        return $this->rateRequestFactory->create(['data' => $requestData]);
    }

    /**
     * Get quote address items
     *
     * @param SubscriptionsCartAddressInterface $address
     * @param SubscriptionsCartInterface $cart
     * @return QuoteAddressItem[]
     */
    private function getQuoteAddressItems(
        SubscriptionsCartAddressInterface $address,
        SubscriptionsCartInterface $cart
    ) {
        $items = $this->itemsRegistry->retrieve($address, $cart);
        return $this->itemsConverterManager->toQuoteAddressItems($items, $address);
    }

    /**
     * Collect carrier rates
     *
     * @param string $carrierCode
     * @param RateRequest $request
     * @return bool|\Magento\Quote\Model\Quote\Address\RateResult\AbstractResult|RateResult
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function collectCarrierRates($carrierCode, $request)
    {
        $result = false;
        $carrier = $this->carrierFactory->createIfActive($carrierCode);
        if ($carrier) {
            $carrier->setActiveFlag('active');
            $result = $carrier->checkAvailableShipCountries($request);
            if ($result !== false && !$result instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
                $result = $carrier->proccessAdditionalValidation($request);
            }
            if ($result !== false) {
                if (!$result instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
                    if ($carrier->getConfigData('shipment_requesttype')) {
                        $packages = $this->shipping->composePackagesForCarrier($carrier, $request);
                        if (!empty($packages)) {
                            $sumResults = [];
                            foreach ($packages as $weight => $packageCount) {
                                $request->setPackageWeight($weight);
                                $result = $carrier->collectRates($request);
                                if (!$result) {
                                    return false;
                                } else {
                                    $result->updateRatePrice($packageCount);
                                }
                                $sumResults[] = $result;
                            }
                            if (!empty($sumResults) && count($sumResults) > 1) {
                                $result = [];
                                foreach ($sumResults as $res) {
                                    if (empty($result)) {
                                        $result = $res;
                                        continue;
                                    }
                                    foreach ($res->getAllRates() as $method) {
                                        foreach ($result->getAllRates() as $resultMethod) {
                                            if ($method->getMethod() == $resultMethod->getMethod()) {
                                                $resultMethod->setPrice(
                                                    $method->getPrice() + $resultMethod->getPrice()
                                                );
                                                continue;
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $result = $carrier->collectRates($request);
                        }
                    } else {
                        $result = $carrier->collectRates($request);
                    }
                }
                if ($carrier->getConfigData('showmethod') == 0 && $result->getError()) {
                    return $this;
                }
            }
        }

        return $result;
    }
}
