<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Mapper;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Api\MapperInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Resolver\Region as RegionResolver;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class Addresses
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api\Mapper
 */
class Addresses implements MapperInterface
{
    /**
     * @var array
     */
    private $toApiMaps = [
        [
            'methods' => [
                Api::CREATE_SUBSCRIPTION_REQUEST,
                Api::UPDATE_SUBSCRIPTION_REQUEST
            ],
            'map' => [
                'billing_address/email' => 'subscription/customer/email',
                'billing_address/telephone' => 'subscription/customer/phoneNumber',
                'billing_address/firstname' => 'subscription/billTo/firstName',
                'billing_address/lastname' => 'subscription/billTo/lastName',
                'billing_address/street' => 'subscription/billTo/address',
                'billing_address/city' => 'subscription/billTo/city',
                'billing_address/region_id' => 'subscription/billTo/state',
                'billing_address/postcode' => 'subscription/billTo/zip',
                'billing_address/country_id' => 'subscription/billTo/country',
                'shipping_address/firstname' => 'subscription/shipTo/firstName',
                'shipping_address/lastname' => 'subscription/shipTo/lastName',
                'shipping_address/street' => 'subscription/shipTo/address',
                'shipping_address/city' => 'subscription/shipTo/city',
                'shipping_address/region_id' => 'subscription/shipTo/state',
                'shipping_address/postcode' => 'subscription/shipTo/zip',
                'shipping_address/country_id' => 'subscription/shipTo/country'
            ]
        ]
    ];

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * @var RegionResolver
     */
    private $regionResolver;

    /**
     * @param ArrayManager $arrayManager
     * @param AddressHelper $addressHelper
     * @param RegionResolver $regionResolver
     */
    public function __construct(
        ArrayManager $arrayManager,
        AddressHelper $addressHelper,
        RegionResolver $regionResolver
    ) {
        $this->arrayManager = $arrayManager;
        $this->addressHelper = $addressHelper;
        $this->regionResolver = $regionResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function toApi($method, $data)
    {
        $result = [];
        $map = $this->getMap($this->toApiMaps, $method);
        if ($map) {
            foreach ($map as $fromPath => $toPath) {
                if ($this->arrayManager->exists($fromPath, $data)) {
                    if (in_array($fromPath, ['billing_address/street', 'shipping_address/street'])) {
                        $result = $this->mapAddressStreet($data, $fromPath, $toPath, $result);
                    } elseif (in_array($fromPath, ['billing_address/region_id', 'shipping_address/region_id'])) {
                        $result = $this->mapRegionCode($data, $fromPath, $toPath, $result);
                    } else {
                        $value = $this->arrayManager->get($fromPath, $data);
                        $result = $this->arrayManager->set($toPath, $result, $value);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Map street data
     *
     * @param array $data
     * @param string $fromPath
     * @param string $toPath
     * @param array $resultData
     * @return array
     */
    private function mapAddressStreet($data, $fromPath, $toPath, $resultData)
    {
        $streetData = $this->arrayManager->get($fromPath, $data);
        $street = $this->addressHelper->convertStreetLines($streetData, 1);
        return $this->arrayManager->set($toPath, $resultData, array_shift($street));
    }

    /**
     * Map region code
     *
     * @param array $data
     * @param string $fromPath
     * @param string $toPath
     * @param array $resultData
     * @return array
     */
    private function mapRegionCode($data, $fromPath, $toPath, $resultData)
    {
        $fromParts = explode('/', $fromPath);
        $addressKey = $fromParts[0];
        $addressData = $data[$addressKey];
        if (isset($addressData[SubscriptionsCartAddressInterface::REGION])
            && isset($addressData[SubscriptionsCartAddressInterface::COUNTRY_ID])
        ) {
            $regionCode = $this->regionResolver->getRegionCode(
                $addressData[SubscriptionsCartAddressInterface::REGION_ID],
                $addressData[SubscriptionsCartAddressInterface::REGION],
                $addressData[SubscriptionsCartAddressInterface::COUNTRY_ID]
            );
            if ($regionCode) {
                return $this->arrayManager->set($toPath, $resultData, $regionCode);
            }
        }
        return $resultData;
    }

    /**
     * {@inheritdoc}
     */
    public function fromApi($method, $data)
    {
        return [];
    }

    /**
     * Get map for method
     *
     * @param array $maps
     * @param string $method
     * @param array|null $default
     * @return array|null
     */
    private function getMap($maps, $method, $default = null)
    {
        foreach ($maps as $mapData) {
            if (in_array($method, $mapData['methods'])) {
                return $mapData['map'];
            }
        }
        return $default;
    }
}
