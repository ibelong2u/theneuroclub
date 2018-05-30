/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko',
        'underscore',
        'mage/translate',
        'Aheadworks_Sarp/js/ui/cart/model/cart'
    ],
    function (ko, _, $t, cart) {
        'use strict';

        /**
         * Init total observable
         *
         * @param {String} totalName
         * @returns {Function}
         */
        function initTotalObservable(totalName) {
            var initValue = cart.cartData() ? cart.cartData()[totalName] : null,
                observable = ko.observable(initValue);

            cart.cartData.subscribe(function (cartData) {
                if (cartData) {
                    observable(cartData[totalName]);
                }
            });

            return observable;
        }

        /**
         * Init totals
         *
         * @returns {Array}
         */
        function initTotals () {
            var cartTotals = [];

            _.each(cartTotalsConfig, function (totalConfig, totalCode) {
                cartTotals.push({
                    'code': totalCode,
                    'title': totalConfig.title,
                    'value': initTotalObservable(totalConfig.fieldName),
                    'details': 'detailsFieldName' in totalConfig
                        ? initTotalObservable(totalConfig.detailsFieldName)
                        : ko.observable(null),
                    'isHidden': 'isHiddenLinkageFieldName' in totalConfig
                        ? initTotalObservable(totalConfig.isHiddenLinkageFieldName)
                        : ko.observable(false)
                });
            });

            return cartTotals;
        }

        var cartTotalsConfig = {
                'subtotal': {
                    'fieldName': 'subtotal',
                    'title': $t('Regular Payment Cart Subtotal')
                },
                'shipping_amount': {
                    'fieldName': 'shipping_amount',
                    'title': $t('Shipping'),
                    'detailsFieldName': 'shipping_description',
                    'isHiddenLinkageFieldName': 'is_virtual'
                },
                'tax_amount': {
                    'fieldName': 'tax_amount',
                    'title': $t('Tax')
                },
                'grand_total': {
                    'fieldName': 'grand_total',
                    'title': $t('Regular Payment Total')
                }
            },
            totals = initTotals();

        return {
            /**
             * Get totals
             *
             * @returns {Array}
             */
            getTotals: function () {
                return totals;
            }
        };
    }
);
