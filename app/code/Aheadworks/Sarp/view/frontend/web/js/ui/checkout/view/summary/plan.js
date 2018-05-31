/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'uiComponent',
        'underscore',
        'Aheadworks_Sarp/js/ui/checkout/model/plan',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Magento_Catalog/js/price-utils',
        'moment',
        'mageUtils'
    ],
    function (Component, _, plan, cart, priceUtils, moment, utils) {
        'use strict';

        return Component.extend({
            plan: plan.getPlan(),

            /**
             * Get start value
             *
             * @returns {String}
             */
            getStartValue: function () {
                return plan.getPlan().start_date_type == 'defined_by_customer'
                    ? this._formatDate(cart.startDate())
                    : plan.getPlan().start;
            },

            /**
             * Get regular price html
             *
             * @returns {String}
             */
            getRegularPrice: function () {
                return this._formatPrice(cart.cartData().subtotal);
            },

            /**
             * Get trial price html
             *
             * @returns {String}
             */
            getTrialPrice: function () {
                return this._formatPrice(cart.cartData().trial_subtotal);
            },

            /**
             * Get trial price value
             *
             * @returns {String}
             */
            getTrialPriceValue: function () {
                return cart.cartData().trial_subtotal;
            },

            /**
             * Get initial fee html
             *
             * @returns {String}
             */
            getInitialFee: function () {
                return this._formatPrice(cart.cartData().initial_fee);
            },

            /**
             * Format date value
             *
             * @param {String} value
             * @returns {String}
             * @private
             */
            _formatDate: function (value) {
                var inputDateFormat = this._convertToMomentFormat(window.awSarpCheckoutConfig.dateFormat),
                    outputDateFormat = 'y-MM-dd',
                    dateValue = moment(value, utils.normalizeDate(outputDateFormat));

                return moment(dateValue).format(inputDateFormat);
            },

            /**
             * Format price amount
             *
             * @param {Number} price
             * @returns {String}
             * @private
             */
            _formatPrice: function (price) {
                return priceUtils.formatPrice(price, cart.getPriceFormat());
            },

            /**
             * Converts PHP IntlFormatter format to moment format
             *
             * @param {String} format
             * @returns {String}
             * @private
             */
            _convertToMomentFormat: function (format) {
                return format.replace(/yy|y/gi, 'YYYY')
                    .replace(/dd|d/g, 'DD');
            }
        });
    }
);
