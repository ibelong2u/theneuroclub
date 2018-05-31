/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'underscore',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Aheadworks_Sarp/js/ui/checkout/model/payment-method',
        'Magento_Checkout/js/model/payment/method-list',
        'Aheadworks_Sarp/js/ui/checkout/action/select-payment-method'
    ],
    function (_, cart, paymentMethod, methodList, selectPaymentMethod) {
        'use strict';

        return {

            /**
             * Populate the list of payment methods
             *
             * @param {Array} methods
             */
            setPaymentMethods: function (methods) {
                var methodIsAvailable;

                if (methods.length === 1) {
                    selectPaymentMethod(methods[0]);
                } else if (paymentMethod.method()) {
                    methodIsAvailable = methods.some(function (item) {
                        return item.method === paymentMethod.method().method_code;
                    });
                    if (!methodIsAvailable) {
                        selectPaymentMethod(null);
                    }
                }

                methodList(methods);
            },

            /**
             * Get the list of available payment methods
             *
             * @returns {Array}
             */
            getAvailablePaymentMethods: function () {
                var methods = [];

                _.each(methodList(), function (method) {
                    if (cart.cartData().grand_total > 0) {
                        methods.push(method);
                    }
                });

                return methods;
            }
        };
    }
);
