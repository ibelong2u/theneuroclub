/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'mage/storage',
        'Aheadworks_Sarp/js/ui/model/url-builder',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-service',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Aheadworks_Sarp/js/ui/model/error-processor'
    ],
    function (
        storage,
        urlBuilder,
        cart,
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
            getRates: function(address) {
                var cache = rateRegistry.get(address.getKey()),
                    serviceUrl = urlBuilder.createUrl('/awSarp/estimate-shipping-methods-by-customer-address-id', {}),
                    payload = {
                        cartId: cart.getCartId(),
                        customerAddressId: address.customerAddressId
                    };

                shippingService.isLoading(true);

                if (cache) {
                    shippingService.setShippingRates(cache);
                    shippingService.isLoading(false);
                } else {
                    storage.post(
                        serviceUrl, JSON.stringify(payload), false
                    ).done(
                        function(result) {
                            rateRegistry.set(address.getKey(), result);
                            shippingService.setShippingRates(result);
                        }
                    ).fail(
                        function(response) {
                            shippingService.setShippingRates([]);
                            errorProcessor.process(response);
                        }
                    ).always(
                        function () {
                            shippingService.isLoading(false);
                        }
                    );
                }
            }
        };
    }
);
