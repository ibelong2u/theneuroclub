/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko',
        'mage/storage',
        'Aheadworks_Sarp/js/ui/model/url-builder',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Magento_Customer/js/model/customer',
        'Aheadworks_Sarp/js/ui/checkout/model/address-converter',
        'Aheadworks_Sarp/js/ui/checkout/model/billing-address',
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-address',
        'Aheadworks_Sarp/js/ui/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Aheadworks_Sarp/js/ui/checkout/action/select-billing-address'
    ],
    function (
        ko,
        storage,
        urlBuilder,
        cart,
        customer,
        addressConverter,
        billingAddress,
        shippingAddress,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction
    ) {
        'use strict';

        return {
            saveShippingInformation: function () {
                var payload,
                    serviceUrl = customer.isLoggedIn()
                        ? urlBuilder.createUrl('/awSarp/save-shipping-information', {})
                        : urlBuilder.createUrl('/awSarp/save-guest-shipping-information', {});

                if (!billingAddress.address()) {
                    selectBillingAddressAction(shippingAddress.address());
                }

                payload = {
                    cartId: cart.getCartId(),
                    shippingInformation: {
                        shipping_address: addressConverter.getAddressDataForRequest(
                            shippingAddress.address(),
                            'shipping'
                        ),
                        billing_address: addressConverter.getAddressDataForRequest(
                            billingAddress.address(),
                            'billing'
                        ),
                        shipping_method_code: cart.shippingMethod().method_code,
                        shipping_carrier_code: cart.shippingMethod().carrier_code
                    }
                };
                if (!customer.isLoggedIn()) {
                    payload.email = cart.guestEmail;
                }

                fullScreenLoader.startLoader();

                return storage.post(
                    serviceUrl, JSON.stringify(payload)
                ).done(
                    function (response) {
                        cart.setCartData(response);
                        fullScreenLoader.stopLoader();
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        fullScreenLoader.stopLoader();
                    }
                );
            }
        };
    }
);
