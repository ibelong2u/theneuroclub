/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'Aheadworks_Sarp/js/ui/checkout/view/payment/cc-form',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Aheadworks_Sarp/js/ui/checkout/action/create-profile',
        'Magento_Checkout/js/action/redirect-on-success',
        'Aheadworks_Sarp/js/ui/checkout/model/billing-address',
        'stripeJs'
    ],
    function (
        $,
        Component,
        additionalValidators,
        createProfileAction,
        redirectOnSuccessAction,
        billingAddress
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_Sarp/checkout/payment/method-renderer/stripe',
                timeoutMessage: 'Sorry, but something went wrong. Please contact the seller.',
                formSelector: '[data-role=stripe-cc-form]',
                token: ''
            },

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super();
                Stripe.setPublishableKey(this._getPublishableKey());
            },

            /**
             * Get publishable api key
             *
             * @returns {String}
             * @private
             */
            _getPublishableKey: function () {
                return window.checkoutConfig.payment[this.getCode()].publishableKey;
            },

            /**
             * @inheritdoc
             */
            isShowLegend: function () {
                return true;
            },

            /**
             * @inheritdoc
             */
            getData: function () {
                return {
                    'method_code': this.item.method,
                    'payment_data': {
                        'token': this.token
                    }
                };
            },

            /**
             * @inheritdoc
             */
            getCode: function () {
                return 'stripe';
            },

            /**
             * @inheritdoc
             */
            isActive: function () {
                return true;
            },

            /**
             * @inheritdoc
             */
            validate: function () {
                var form = $(this.formSelector);

                form.validation();

                return form.validation('isValid');
            },

            /**
             * @inheritdoc
             */
            createProfile: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }
                if (this.validate() && additionalValidators.validate()) {
                    this.isCreateProfileActionAllowed(false);
                    Stripe.card.createToken(
                        this._getStripeCardDataForToken(),
                        function (status, response) {
                            if (response.error) {
                                self.messageContainer.addErrorMessage(response.error.message);
                                self.isCreateProfileActionAllowed(true);
                            } else {
                                self.token = response.id;

                                $.when(
                                    createProfileAction(self.getData(), self.messageContainer)
                                ).fail(
                                    function () {
                                        self.isCreateProfileActionAllowed(true);
                                    }
                                ).done(
                                    function () {
                                        redirectOnSuccessAction.execute();
                                    }
                                );
                            }
                        }
                    );
                    return true;
                }

                return false;
            },

            /**
             * Get card data to create a token
             *
             * @returns {Object}
             * @private
             */
            _getStripeCardDataForToken: function () {
                var billingAddressData = billingAddress.address(),
                    data = {
                        number: this.creditCardNumber(),
                        exp_month: this.creditCardExpMonth(),
                        exp_year: this.creditCardExpYear(),
                        address_line1: billingAddressData.street[0],
                        address_line2: billingAddressData.street[1] || null,
                        address_city: billingAddressData.city,
                        address_state: billingAddressData.region,
                        address_zip: billingAddressData.postcode,
                        address_country: billingAddressData.countryId
                    };

                if (this.hasVerification()) {
                    data.cvc = this.creditCardVerificationNumber();
                }

                return data;
            }
        });
    }
);
