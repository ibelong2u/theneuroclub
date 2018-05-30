/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'uiComponent',
        'underscore',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function (Component, _, cart, stepNavigator) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_Sarp/checkout/summary/cart-items'
            },
            getItems: cart.cartItems,
            itemsQty: 0,

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super()
                    ._initItemsQty();

                return this;
            },

            /**
             * Init items qty value
             *
             * @returns {exports}
             * @private
             */
            _initItemsQty: function () {
                var itemsQty = 0;

                _.each(this.getItems(), function (item) {
                    itemsQty += item.qty;
                });
                this.itemsQty = itemsQty;

                return this;
            },

            /**
             * Get items qty
             *
             * @returns {Number}
             */
            getItemsQty: function() {
                return parseFloat(this.itemsQty);
            },

            /**
             * Check if block expanded
             *
             * @returns {boolean}
             */
            isItemsBlockExpanded: function () {
                return cart.cartData().is_virtual || stepNavigator.isProcessed('shipping');
            }
        });
    }
);
