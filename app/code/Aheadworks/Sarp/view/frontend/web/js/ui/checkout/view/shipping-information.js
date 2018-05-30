/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'uiComponent',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/sidebar'
    ],
    function($, Component, cart, stepNavigator, sidebarModel) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-information'
            },

            /**
             * Check if visible
             *
             * @returns {boolean}
             */
            isVisible: function() {
                return !cart.cartData().is_virtual && stepNavigator.isProcessed('shipping');
            },

            /**
             * Get shipping method title
             *
             * @returns {string}
             */
            getShippingMethodTitle: function() {
                var shippingMethod = cart.shippingMethod();

                return shippingMethod ? shippingMethod.carrier_title + ' - ' + shippingMethod.method_title : '';
            },

            /**
             * Back to edit shipping handler
             */
            back: function() {
                sidebarModel.hide();
                stepNavigator.navigateTo('shipping');
            },

            /**
             * Back to shipping method handler
             */
            backToShippingMethod: function() {
                sidebarModel.hide();
                stepNavigator.navigateTo('shipping', 'opc-shipping_method');
            }
        });
    }
);
