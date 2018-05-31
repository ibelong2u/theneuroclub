/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'underscore',
        'uiComponent',
        'ko',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Magento_Checkout/js/model/step-navigator',
        'Aheadworks_Sarp/js/ui/checkout/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Aheadworks_Sarp/js/ui/checkout/model/checkout-data-resolver',
        'mage/translate'
    ],
    function (
        $,
        _,
        Component,
        ko,
        cart,
        stepNavigator,
        paymentService,
        methodConverter,
        checkoutDataResolver,
        $t
    ) {
        'use strict';

        paymentService.setPaymentMethods(
            methodConverter(window.awSarpCheckoutConfig.paymentMethods)
        );

        return Component.extend({
            defaults: {
                template: 'Aheadworks_Sarp/checkout/payment',
                activeMethod: ''
            },
            isVisible: ko.observable(cart.cartData().is_virtual),
            cartIsVirtual: cart.cartData().is_virtual,
            isPaymentMethodsAvailable: ko.computed(function () {
                return paymentService.getAvailablePaymentMethods().length > 0;
            }),

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super()
                    ._registerStepNavigatorStep();
                checkoutDataResolver.resolvePaymentMethod();

                return this;
            },

            /**
             * Navigate to payment step
             */
            navigate: function () {
                checkoutDataResolver.resolveBillingAddress();
                this.isVisible(true);
            },

            /**
             * Register step navigator step
             *
             * @returns {exports}
             * @private
             */
            _registerStepNavigatorStep: function () {
                stepNavigator.registerStep(
                    'payment',
                    null,
                    $t('Review & Payments'),
                    this.isVisible, _.bind(this.navigate, this),
                    20
                );

                return this;
            },

            /**
             * Get form key
             *
             * @returns {string}
             */
            getFormKey: function() {
                return window.awSarpCheckoutConfig.formKey;
            }
        });
    }
);
