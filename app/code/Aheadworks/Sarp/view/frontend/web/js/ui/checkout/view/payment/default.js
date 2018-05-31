/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'Aheadworks_Sarp/js/ui/checkout/action/create-profile',
        'Aheadworks_Sarp/js/ui/checkout/action/select-payment-method',
        'Aheadworks_Sarp/js/ui/checkout/model/payment-method',
        'Aheadworks_Sarp/js/ui/checkout/model/billing-address',
        'Aheadworks_Sarp/js/ui/checkout/model/payment-service',
        'Aheadworks_Sarp/js/ui/checkout/checkout-data',
        'Aheadworks_Sarp/js/ui/checkout/model/checkout-data-resolver',
        'uiRegistry',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/model/messages',
        'uiLayout',
        'Magento_Checkout/js/action/redirect-on-success'
    ],
    function (
        ko,
        $,
        Component,
        createProfileAction,
        selectPaymentMethodAction,
        paymentMethod,
        billingAddress,
        paymentService,
        checkoutData,
        checkoutDataResolver,
        registry,
        additionalValidators,
        Messages,
        layout,
        redirectOnSuccessAction
    ) {
        'use strict';

        return Component.extend({
            isCreateProfileActionAllowed: ko.observable(billingAddress.address() != null),

            /**
             * @inheritdoc
             */
            initialize: function () {
                var billingAddressCode,
                    billingAddressData,
                    defaultAddressData;

                this._super().initChildren();
                billingAddress.address.subscribe(function (address) {
                    this.isCreateProfileActionAllowed(address !== null);
                }, this);

                billingAddressCode = 'billingAddress' + this.getCode();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    defaultAddressData = checkoutProvider.get(billingAddressCode);

                    if (defaultAddressData !== undefined) {
                        billingAddressData = checkoutData.getBillingAddressFromData();

                        if (billingAddressData) {
                            checkoutProvider.set(
                                billingAddressCode,
                                $.extend(true, {}, defaultAddressData, billingAddressData)
                            );
                        }
                        checkoutProvider.on(billingAddressCode, function (providerBillingAddressData) {
                            checkoutData.setBillingAddressFromData(providerBillingAddressData);
                        }, billingAddressCode);
                    }
                });

                return this;
            },

            /**
             * Initialize child elements
             *
             * @returns {Component} Chainable.
             */
            initChildren: function () {
                this.messageContainer = new Messages();
                this.createMessagesComponent();

                return this;
            },

            /**
             * Create child message renderer component
             *
             * @returns {Component} Chainable.
             */
            createMessagesComponent: function () {
                var messagesComponent = {
                    parent: this.name,
                    name: this.name + '.messages',
                    displayArea: 'messages',
                    component: 'Magento_Ui/js/view/messages',
                    config: {
                        messageContainer: this.messageContainer
                    }
                };

                layout([messagesComponent]);

                return this;
            },

            /**
             * Create profile handler
             *
             * @param {Object} data
             * @param {Event} event
             * @returns {boolean}
             */
            createProfile: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }
                if (this.validate() && additionalValidators.validate()) {
                    this.isCreateProfileActionAllowed(false);

                    $.when(
                        createProfileAction(this.getData(), this.messageContainer)
                    ).fail(
                        function () {
                            self.isCreateProfileActionAllowed(true);
                        }
                    ).done(
                        function () {
                            redirectOnSuccessAction.execute();
                        }
                    );

                    return true;
                }

                return false;
            },

            /**
             * Select payment method handler
             *
             * @returns {Boolean}
             */
            selectPaymentMethod: function () {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);

                return true;
            },

            /**
             * Check if radio button checked
             *
             * @returns {Boolean}
             */
            isChecked: ko.computed(function () {
                return paymentMethod.method() ? paymentMethod.method().method : null;
            }),

            /**
             * Check if radio button is visible
             *
             * @returns {Boolean}
             */
            isRadioButtonVisible: ko.computed(function () {
                return paymentService.getAvailablePaymentMethods().length !== 1;
            }),

            /**
             * Get payment method data
             *
             * @returns {Object}
             */
            getData: function () {
                return {
                    'method_code': this.item.method,
                    'payment_data': null
                };
            },

            /**
             * Get payment method type
             *
             * @returns {String}
             */
            getTitle: function () {
                return this.item.title;
            },

            /**
             * Get payment method code
             *
             * @returns {String}
             */
            getCode: function () {
                return this.item.method;
            },

            /**
             * @returns {Boolean}
             */
            validate: function () {
                return true;
            },

            /**
             * Get billing address form name
             *
             * @returns {String}
             */
            getBillingAddressFormName: function () {
                return 'billing-address-form-' + this.item.method;
            },

            /**
             * Dispose billing address subscriptions
             */
            disposeSubscriptions: function () {
                var billingAddressCode = 'billingAddress' + this.getCode();

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    checkoutProvider.off(billingAddressCode);
                });
            }
        });
    }
);
