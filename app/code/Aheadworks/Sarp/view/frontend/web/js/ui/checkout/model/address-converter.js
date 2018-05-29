/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'Magento_Checkout/js/model/new-customer-address',
        'Magento_Customer/js/customer-data',
        'mage/utils/objects'
    ],
    function($, address, customerData, mageUtils) {
        'use strict';

        var countryData = customerData.get('directory-data');

        return {
            /**
             * Convert address form data to Address object
             *
             * @param {Object} formData
             * @returns {Object}
             */
            formAddressDataToCartAddress: function(formData) {
                var addressData = $.extend(true, {}, formData),
                    region,
                    regionName = addressData.region;

                if (mageUtils.isObject(addressData.street)) {
                    addressData.street = this._objectToArray(addressData.street);
                }

                addressData.region = {
                    region_id: addressData.region_id,
                    region_code: addressData.region_code,
                    region: regionName
                };

                if (addressData.region_id
                    && countryData()[addressData.country_id]
                    && countryData()[addressData.country_id]['regions']
                ) {
                    region = countryData()[addressData.country_id]['regions'][addressData.region_id];
                    if (region) {
                        addressData.region.region_id = addressData['region_id'];
                        addressData.region.region_code = region['code'];
                        addressData.region.region = region['name'];
                    }
                } else if (
                    !addressData.region_id
                    && countryData()[addressData.country_id]
                    && countryData()[addressData.country_id]['regions']
                ) {
                    addressData.region.region_code = '';
                    addressData.region.region = '';
                }
                delete addressData.region_id;

                return address(addressData);
            },

            /**
             * Get address data for request
             *
             * @param {Object} address
             * @param {String} addressType
             * @returns {Object}
             */
            getAddressDataForRequest: function (address, addressType) {
                return {
                    'address_type': addressType,
                    'street': address.street,
                    'city': address.city,
                    'region_id': address.regionId,
                    'region': address.region,
                    'country_id': address.countryId,
                    'postcode': address.postcode,
                    'email': address.email,
                    'customer_id': address.customerId,
                    'firstname': address.firstname,
                    'lastname': address.lastname,
                    'middlename': address.middlename,
                    'prefix': address.prefix,
                    'suffix': address.suffix,
                    'vat_id': address.vatId,
                    'company': address.company,
                    'telephone': address.telephone,
                    'fax': address.fax,
                    'customer_address_id': address.customerAddressId,
                    'is_save_in_address_book': address.saveInAddressBook
                };
            },

            /**
             * Convert form data to flat data
             *
             * @param {Object} formProviderData
             * @param {String} formIndex
             * @returns {Object}
             */
            formDataProviderToFlatData: function(formProviderData, formIndex) {
                var addressData = {};

                $.each(formProviderData, function(path, value) {
                    var pathComponents = path.split('.'),
                        dataObject = {};

                    pathComponents.splice(pathComponents.indexOf(formIndex), 1);
                    pathComponents.reverse();
                    $.each(pathComponents, function(index, pathPart) {
                        var parent;

                        if (index == 0) {
                            dataObject[pathPart] = value;
                        } else {
                            parent = {};
                            parent[pathPart] = dataObject;
                            dataObject = parent;
                        }
                    });
                    $.extend(true, addressData, dataObject);
                });

                return addressData;
            },

            /**
             * Convert object to array
             *
             * @param {Object} object
             * @returns {Array}
             */
            _objectToArray: function (object) {
                var convertedArray = [];

                $.each(object, function (key) {
                    return typeof object[key] === 'string' ? convertedArray.push(object[key]) : false;
                });

                return convertedArray.slice(0);
            }
        };
    }
);
