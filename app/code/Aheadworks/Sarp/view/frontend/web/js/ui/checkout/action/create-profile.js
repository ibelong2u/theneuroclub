/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Magento_Customer/js/model/customer',
        'Aheadworks_Sarp/js/ui/checkout/model/billing-address',
        'Magento_Checkout/js/model/url-builder',
        'Aheadworks_Sarp/js/ui/checkout/model/create-profile',
        'Aheadworks_Sarp/js/ui/checkout/model/address-converter'
    ],
    function (
        cart,
        customer,
        billingAddress,
        urlBuilder,
        createProfileService,
        addressConverter
    ) {
        'use strict';

        return function (paymentData, messageContainer) {
            // Server-side implementation for subscription engines with direct submit profile logic
            var serviceUrl = customer.isLoggedIn()
                ? urlBuilder.createUrl('/awSarp/save-payment-information-and-submit', {})
                : urlBuilder.createUrl('/awSarp/save-guest-payment-information-and-submit', {}),
                payload  = {
                    cartId: cart.getCartId(),
                    paymentInformation: paymentData
                };

            if (billingAddress.address()) {
                payload.billingAddress = addressConverter.getAddressDataForRequest(billingAddress.address(), 'billing');
            }
            if (!customer.isLoggedIn()) {
                payload.email = cart.guestEmail;
            }

            return createProfileService(serviceUrl, payload, messageContainer);
        };
    }
);
