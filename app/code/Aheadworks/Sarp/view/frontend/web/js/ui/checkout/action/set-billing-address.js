/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'mage/storage',
        'Aheadworks_Sarp/js/ui/model/url-builder',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Magento_Customer/js/model/customer',
        'Aheadworks_Sarp/js/ui/checkout/model/address-converter',
        'Aheadworks_Sarp/js/ui/checkout/model/billing-address',
        'Aheadworks_Sarp/js/ui/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (
        storage,
        urlBuilder,
        cart,
        customer,
        addressConverter,
        billingAddress,
        errorProcessor,
        fullScreenLoader
    ) {
        'use strict';

        return function (messageContainer) {
            var serviceUrl = customer.isLoggedIn()
                    ? urlBuilder.createUrl('/awSarp/save-billing-address', {})
                    : urlBuilder.createUrl('/awSarp/save-guest-billing-address', {}),
                payload = {
                    cartId: cart.getCartId(),
                    billingAddress: addressConverter.getAddressDataForRequest(billingAddress.address(), 'billing')
                };

            if (!customer.isLoggedIn()) {
                payload.email = cart.guestEmail;
            }
            fullScreenLoader.startLoader();

            return storage.post(
                serviceUrl, JSON.stringify(payload)
            ).done(
                function () {
                    fullScreenLoader.stopLoader();
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                    fullScreenLoader.stopLoader();
                }
            );
        };
    }
);
