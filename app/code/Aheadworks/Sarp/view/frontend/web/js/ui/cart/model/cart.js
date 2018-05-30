/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'ko',
        'underscore'
    ],
    function($, ko, _) {
        'use strict';

        var cartData = ko.observable(window.awSarpCheckoutConfig.subscriptionsCart),
            cartItems = ko.observableArray(cartData().items),
            planId = ko.observable(cartData().subscription_plan_id),
            isPlanSelected = ko.observable(!!planId()),
            startDate = ko.observable(cartData().start_date),
            shippingMethod = ko.observable(null);

        cartData.subscribe(function (data) {
            cartItems(data.items);
            planId(data.subscription_plan_id);
            isPlanSelected(!!planId());
            startDate(data.start_date);
        });
        startDate.subscribe(function (startDate) {
            cartData().start_date = startDate;
        });

        return {
            cartData: cartData,
            cartItems: cartItems,
            planId: planId,
            isPlanSelected: isPlanSelected,
            startDate: startDate,
            shippingMethod: shippingMethod,
            guestEmail: null,

            /**
             * Get cart Id
             *
             * @returns {number}
             */
            getCartId: function () {
                return cartData().cart_id;
            },

            /**
             * Get price format
             *
             * @returns {Object}
             */
            getPriceFormat: function () {
                return window.awSarpCheckoutConfig.priceFormat;
            },

            /**
             * Get items count
             *
             * @returns {number}
             */
            getItemsCount: function () {
                var itemsCount = 0;

                $.each(this.cartItems(), function () {
                    if (!this.is_deleted) {
                        itemsCount++;
                    }
                });

                return itemsCount;
            },

            /**
             * Get subscription plan Id
             *
             * @returns {number}
             */
            getSubscriptionPlanId: function () {
                return this.planId();
            },

            /**
             * Get is subscription plan selected flag
             *
             * @returns {boolean}
             */
            isSubscriptionPlanSelected: function () {
                return this.isPlanSelected();
            },

            /**
             * Set cart data
             *
             * @param {Array} cartData
             */
            setCartData: function (cartData) {
                this.cartData(cartData);
            },

            /**
             * Get address by type
             *
             * @param {String} addressType
             * @returns {Object}
             */
            getAddressByType: function (addressType) {
                var cartAddresses = this.cartData().addresses;

                return cartAddresses
                    ? _.find(cartAddresses, function (address) {
                        return address.address_type == addressType
                    })
                    : {};
            }
        };
    }
);
