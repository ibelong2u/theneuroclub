/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'uiComponent',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Aheadworks_Sarp/js/ui/checkout/model/totals',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function (Component, cart, totals, priceUtils, stepNavigator) {
        'use strict';

        return Component.extend({
            totals: totals.getTotals(),

            /**
             * Format price amount
             *
             * @param {Number} price
             * @returns {String}
             */
            formatPrice: function (price) {
                return priceUtils.formatPrice(price, cart.getPriceFormat());
            },

            /**
             * Check if full mode
             *
             * @returns {boolean}
             */
            isFullMode: function() {
                return stepNavigator.isProcessed('shipping');
            },

            /**
             * Check if displayed
             *
             * @return {boolean}
             */
            isDisplayed: function () {
                return this.isFullMode();
            }
        });
    }
);
