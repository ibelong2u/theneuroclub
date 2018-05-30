/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'mage/storage',
        'Aheadworks_Sarp/js/ui/model/url-builder',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Aheadworks_Sarp/js/ui/checkout/model/address-converter',
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-service',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Aheadworks_Sarp/js/ui/model/error-processor'
    ],
    function (
        storage,
        urlBuilder,
        cart,
        addressConverter,
        shippingService,
        rateRegistry,
        errorProcessor
    ) {
        'use strict';

        return {
            /**
             * Get shipping rates for specified address
             *
             * @param {Object} address
             */
            getRates: function (address) {
                var cache = rateRegistry.get(address.getCacheKey()),
                    serviceUrl = urlBuilder.createUrl('/awSarp/estimate-shipping-methods', {}),
                    payload = {
                        cartId: cart.getCartId(),
                        shippingAddress: this._prepareAddressData(address)
                    };

                shippingService.isLoading(true);

                if (cache) {
                    shippingService.setShippingRates(cache);
                    shippingService.isLoading(false);
                } else {
                    storage.post(
                        serviceUrl, JSON.stringify(payload), false
                    ).done(
                        function (result) {
                            rateRegistry.set(address.getCacheKey(), result);
                            shippingService.setShippingRates(result);
                        }
                    ).fail(
                        function (response) {
                            shippingService.setShippingRates([]);
                            errorProcessor.process(response);
                        }
                    ).always(
                        function () {
                            shippingService.isLoading(false);
                        }
                    );
                }
            },

            /**
             * Prepare address data for request
             *
             * @param {Object} address
             * @returns {Object}
             * @private
             */
            _prepareAddressData: function (address) {
                var addressData = addressConverter.getAddressDataForRequest(address, 'shipping');

                addressData.cart_id = cart.getCartId();
                addressData.address_id = cart.getAddressByType('shipping').address_id;

                return addressData;
            }
        };
    }
);
