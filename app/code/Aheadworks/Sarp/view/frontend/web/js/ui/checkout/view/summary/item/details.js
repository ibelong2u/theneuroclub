/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'uiComponent',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Magento_Catalog/js/price-utils'
    ],
    function (Component, cart, priceUtils) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_Sarp/checkout/summary/item/details'
            },
            /**
             * @param cartItem
             * @returns {String}
             */
            getValue: function(cartItem) {
                return cartItem.name;
            },

            /**
             * Format price amount
             *
             * @param {Number} price
             * @returns {String}
             */
            formatPrice: function (price) {
                return priceUtils.formatPrice(price, cart.getPriceFormat());
            }
        });
    }
);
