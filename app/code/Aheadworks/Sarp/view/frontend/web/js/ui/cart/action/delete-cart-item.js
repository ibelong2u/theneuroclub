/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Aheadworks_Sarp/js/ui/model/url-builder',
        'mage/storage',
        'Aheadworks_Sarp/js/ui/cart/model/full-screen-loader',
        'Aheadworks_Sarp/js/ui/model/error-processor'
    ],
    function (
        $,
        cart,
        urlBuilder,
        storage,
        fullScreenLoader,
        errorProcessor
    ) {
        'use strict';

        return function (cartId, itemId) {
            var serviceUrl = urlBuilder.createUrl('/awSarp/delete-cart-item', {}),
                payload = {
                    cartId: cartId,
                    itemId: itemId
                };

            fullScreenLoader.startLoader();

            return storage.post(
                serviceUrl, JSON.stringify(payload)
            ).done(
                function (response) {
                    cart.setCartData(response);
                    if (!cart.getItemsCount()) {
                        window.location = window.awSarpCheckoutConfig.cartUrl;
                    } else {
                        fullScreenLoader.stopLoader();
                    }
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    fullScreenLoader.stopLoader();
                }
            );
        };
    }
);
