/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'Aheadworks_Sarp/js/ui/checkout/view/payment/default',
        'Aheadworks_Sarp/js/ui/checkout/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/customer-data'
    ],
    function (
        $,
        Component,
        setPaymentMethodAction,
        additionalValidators,
        customerData
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_Sarp/checkout/payment/method-renderer/paypal-express'
            },

            /**
             * @inheritdoc
             */
            initObservable: function () {
                this._super();

                return this;
            },

            /**
             * Open acceptance window handler
             *
             * @param {Object} data
             * @param {Event} event
             * @returns {boolean}
             */
            showAcceptanceWindow: function (data, event) {
                window.open(
                    $(event.currentTarget).attr('href'),
                    'olcwhatispaypal',
                    'toolbar=no, location=no,' +
                    ' directories=no, status=no,' +
                    ' menubar=no, scrollbars=yes,' +
                    ' resizable=yes, ,left=0,' +
                    ' top=0, width=400, height=350'
                );

                return false;
            },

            /**
             * Returns payment acceptance mark link path
             *
             * @returns {String}
             */
            getPaymentAcceptanceMarkHref: function () {
                return window.checkoutConfig.payment.paypalExpress.paymentAcceptanceMarkHref;
            },

            /**
             * Returns payment acceptance mark image path
             *
             * @returns {String}
             */
            getPaymentAcceptanceMarkSrc: function () {
                return window.checkoutConfig.payment.paypalExpress.paymentAcceptanceMarkSrc;
            },

            /**
             * Redirect to paypal handler
             *
             * @returns {boolean}
             */
            continueToPayPal: function () {
                if (additionalValidators.validate()) {
                    this.selectPaymentMethod();
                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            customerData.invalidate(['aw-sarp-subscription-cart']);
                            $.mage.redirect(
                                window.checkoutConfig.payment.paypalExpress.redirectUrl
                            );
                        }
                    );

                    return false;
                }
            }
        });
    }
);
