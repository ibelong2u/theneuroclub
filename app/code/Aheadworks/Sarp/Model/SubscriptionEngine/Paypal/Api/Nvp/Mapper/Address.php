<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Mapper;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp as ApiNvp;
use Aheadworks\Sarp\Model\SubscriptionEngine\Api\MapperInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Resolver\FullName as FullNameResolver;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address\Resolver\Region as RegionResolver;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Framework\DataObject\Mapper as DataObjectMapper;

/**
 * Class Address
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Mapper
 */
class Address implements MapperInterface
{
    /**
     * @var array
     */
    private $toApiMaps = [
        [
            'methods' => [
                ApiNvp::SET_EXPRESS_CHECKOUT,
                ApiNvp::UPDATE_RECURRING_PAYMENTS_PROFILE
            ],
            'map' => [
                'billing_address' => [
                    'company' => 'BUSINESS',
                    'customer_notes' => 'NOTETEXT',
                    'email' => 'EMAIL',
                    'firstname' => 'FIRSTNAME',
                    'lastname' => 'LASTNAME',
                    'middlename' => 'MIDDLENAME',
                    'prefix' => 'SALUTATION',
                    'suffix' => 'SUFFIX',
                    'country_id' => 'COUNTRYCODE',
                    'region_code' => 'STATE',
                    'city' => 'CITY',
                    'street' => 'STREET',
                    'street2' => 'STREET2',
                    'postcode' => 'ZIP',
                    'telephone' => 'PHONENUM'
                ],
                'shipping_address' => [
                    'country_id' => 'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE',
                    'region_code' => 'PAYMENTREQUEST_0_SHIPTOSTATE',
                    'city' => 'PAYMENTREQUEST_0_SHIPTOCITY',
                    'street' => 'PAYMENTREQUEST_0_SHIPTOSTREET',
                    'street2' => 'PAYMENTREQUEST_0_SHIPTOSTREET2',
                    'postcode' => 'PAYMENTREQUEST_0_SHIPTOZIP',
                    'telephone' => 'PAYMENTREQUEST_0_SHIPTOPHONENUM'
                ]
            ]
        ]
    ];

    /**
     * @var array
     */
    private $fromApiMaps = [
        [
            'methods' => [
                ApiNvp::GET_EXPRESS_CHECKOUT_DETAILS,
                ApiNvp::CREATE_RECURRING_PAYMENTS_PROFILE
            ],
            'map' => [
                'billing_address' => [
                    'BUSINESS' => 'company',
                    'NOTETEXT' => 'customer_notes',
                    'EMAIL' => 'email',
                    'FIRSTNAME' => 'firstname',
                    'LASTNAME' => 'lastname',
                    'MIDDLENAME' => 'middlename',
                    'SALUTATION' => 'prefix',
                    'SUFFIX' => 'suffix',
                    'COUNTRYCODE' => 'country_id',
                    'CITY' => 'city',
                    'STREET' => 'street',
                    'STREET2' => 'street2',
                    'ZIP' => 'postcode',
                    'PHONENUM' => 'telephone'
                ],
                'shipping_address' => [
                    'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE' => 'country_id',
                    'PAYMENTREQUEST_0_SHIPTOCITY' => 'city',
                    'PAYMENTREQUEST_0_SHIPTOSTREET' => 'street',
                    'PAYMENTREQUEST_0_SHIPTOSTREET2' => 'street2',
                    'PAYMENTREQUEST_0_SHIPTOZIP' => 'postcode',
                    'PAYMENTREQUEST_0_SHIPTOPHONENUM' => 'telephone'
                ]
            ]
        ]
    ];

    /**
     * @var RegionResolver
     */
    private $regionResolver;

    /**
     * @var FullNameResolver
     */
    private $fullNameResolver;

    /**
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * @param RegionResolver $regionResolver
     * @param FullNameResolver $fullNameResolver
     * @param AddressHelper $addressHelper
     */
    public function __construct(
        RegionResolver $regionResolver,
        FullNameResolver $fullNameResolver,
        AddressHelper $addressHelper
    ) {
        $this->regionResolver = $regionResolver;
        $this->fullNameResolver = $fullNameResolver;
        $this->addressHelper = $addressHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function toApi($method, $data)
    {
        $map = $this->getMap($method, $this->toApiMaps);
        if ($map) {
            return array_merge(
                $this->mapBillingAddressToApi($data, $map),
                $this->mapShippingAddressToApi($data, $map)
            );
        }
        return [];
    }

    /**
     * Map billing address to api
     *
     * @param array $data
     * @param array $map
     * @return array
     */
    private function mapShippingAddressToApi($data, $map)
    {
        $result = [];
        if (isset($data['billing_address'])) {
            $billingAddress = $data['billing_address'];
            $result = DataObjectMapper::accumulateByMap(
                $billingAddress,
                $result,
                isset($map['billing_address']) ? $map['billing_address'] : null
            );
            $result = $this->mapAddressStreetToApi(
                $billingAddress,
                $result,
                ['STREET', 'STREET2']
            );
            $result = $this->mapRegionCodeToApi($billingAddress, $result, 'STATE');
        }
        return $result;
    }

    /**
     * Map shipping address to api
     *
     * @param array $data
     * @param array $map
     * @return array
     */
    private function mapBillingAddressToApi($data, $map)
    {
        $result = [];
        if (isset($data['shipping_address'])) {
            $shippingAddress = $data['shipping_address'];
            $result = DataObjectMapper::accumulateByMap(
                $shippingAddress,
                $result,
                isset($map['shipping_address']) ? $map['shipping_address'] : null
            );
            $result = $this->mapRegionCodeToApi($shippingAddress, $result, 'PAYMENTREQUEST_0_SHIPTOSTATE');
            $result = $this->mapAddressStreetToApi(
                $shippingAddress,
                $result,
                ['PAYMENTREQUEST_0_SHIPTOSTREET', 'PAYMENTREQUEST_0_SHIPTOSTREET2']
            );
            $result = $this->mapFullNameToApi($shippingAddress, $result, 'PAYMENTREQUEST_0_SHIPTONAME');
        }
        return $result;
    }

    /**
     * Get map for specific method
     *
     * @param string $method
     * @param array $mapsConfig
     * @return array|null
     */
    private function getMap($method, $mapsConfig)
    {
        foreach ($mapsConfig as $mapData) {
            if (in_array($method, $mapData['methods'])) {
                return $mapData['map'];
            }
        }
        return null;
    }

    /**
     * Map region code to api
     *
     * @param array $addressData
     * @param array $resultData
     * @param string $mapField
     * @return array
     */
    private function mapRegionCodeToApi($addressData, $resultData, $mapField)
    {
        if (isset($addressData[SubscriptionsCartAddressInterface::REGION_ID])
            && isset($addressData[SubscriptionsCartAddressInterface::REGION])
            && isset($addressData[SubscriptionsCartAddressInterface::COUNTRY_ID])
        ) {
            $regionCode = $this->regionResolver->getRegionCode(
                $addressData[SubscriptionsCartAddressInterface::REGION_ID],
                $addressData[SubscriptionsCartAddressInterface::REGION],
                $addressData[SubscriptionsCartAddressInterface::COUNTRY_ID]
            );
            if ($regionCode) {
                $resultData[$mapField] = $regionCode;
            }
        }

        return $resultData;
    }

    /**
     * Map full name to api
     * @param array $addressData
     * @param array $resultData
     * @param string $mapField
     * @return array
     */
    private function mapFullNameToApi($addressData, $resultData, $mapField)
    {
        $resultData[$mapField] = $this->fullNameResolver->getFullName($addressData);
        return $resultData;
    }

    /**
     * Map street data to api
     *
     * @param array|null $addressData
     * @param array $resultData
     * @param array $mapFields
     * @return array
     */
    private function mapAddressStreetToApi($addressData, $resultData, $mapFields)
    {
        if ($addressData && isset($addressData[SubscriptionsCartAddressInterface::STREET])) {
            $street = $this->addressHelper->convertStreetLines(
                $addressData[SubscriptionsCartAddressInterface::STREET],
                count($mapFields)
            );
            $i = 0;
            foreach ($mapFields as $field) {
                $resultData[$field] = isset($street[$i]) ? $street[$i] : '';
                $i++;
            }
        }
        return $resultData;
    }

    /**
     * {@inheritdoc}
     */
    public function fromApi($method, $data)
    {
        $result = [];
        $map = $this->getMap($method, $this->fromApiMaps);
        if ($map) {
            $billingAddressMap = isset($map['billing_address'])
                ? $map['billing_address']
                : null;
            if ($billingAddressMap) {
                $billingAddress = DataObjectMapper::accumulateByMap($data, [], $billingAddressMap);
                $billingAddress = $this->mapAddressStreetFromApi(
                    $data,
                    $billingAddress,
                    ['STREET', 'STREET2']
                );
                $billingAddress = $this->mapRegionDataFromApi($data, $billingAddress, 'STATE', 'COUNTRYCODE');
                $result['billing_address'] = $billingAddress;
            }
            $shippingAddressMap = isset($map['shipping_address'])
                ? $map['shipping_address']
                : null;
            if ($shippingAddressMap) {
                $shippingAddress = DataObjectMapper::accumulateByMap($data, [], $shippingAddressMap);
                $shippingAddress = $this->mapAddressStreetFromApi(
                    $data,
                    $shippingAddress,
                    ['PAYMENTREQUEST_0_SHIPTOSTREET', 'PAYMENTREQUEST_0_SHIPTOSTREET2']
                );
                $shippingAddress = $this->mapRegionDataFromApi(
                    $data,
                    $shippingAddress,
                    'PAYMENTREQUEST_0_SHIPTOSTATE',
                    'PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'
                );
                $result['shipping_address'] = $shippingAddress;
            }
        }
        return $result;
    }

    /**
     * Map region data from api
     *
     * @param array $data
     * @param array $resultData
     * @param string $regionIdMapField
     * @param string $countryIdMapField
     * @return array
     */
    private function mapRegionDataFromApi($data, $resultData, $regionIdMapField, $countryIdMapField)
    {
        if (isset($data[$regionIdMapField]) && isset($data[$countryIdMapField])) {
            $regionId = $data[$regionIdMapField];
            $countryId = $data[$countryIdMapField];
            $resultData['region_id'] = $this->regionResolver->getRegionId($regionId, $countryId);
            $resultData['region'] = $this->regionResolver->getRegionByCode($regionId, $countryId);
        }
        return $resultData;
    }

    /**
     * Map street data from api
     *
     * @param array $data
     * @param array $resultData
     * @param array $mapFields
     * @return array
     */
    private function mapAddressStreetFromApi($data, $resultData, $mapFields)
    {
        $street = [];
        foreach ($mapFields as $field) {
            if (isset($data[$field])) {
                $street[] = $data[$field];
            }
        }

        if (!empty($street)) {
            $resultData['street'] = $street;
        }
        return $resultData;
    }
}
