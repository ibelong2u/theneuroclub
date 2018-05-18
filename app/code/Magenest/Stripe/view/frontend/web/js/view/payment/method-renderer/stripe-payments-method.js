/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Ui/js/model/messages',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url'
    ],
    function (Component, $,ko, quote,customer, fullScreenLoader, redirectOnSuccessAction, messageContainer, additionalValidators, url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magenest_Stripe/payment/stripe-payments-method',
                redirectAfterPlaceOrder: false,
                saveCardConfig: window.checkoutConfig.payment.magenest_stripe.isSave,
                isLogged: window.checkoutConfig.payment.magenest_stripe_config.isLogin,
                saveCardOption: "",
                api_response: "",
                isCardNumberError: ko.observable(false),
                isCardExpError: ko.observable(false),
                isCardCcvError: ko.observable(false),
                customerCard: ko.observableArray(JSON.parse(window.checkoutConfig.payment.magenest_stripe.saveCards)),
                cardId: ko.observable(0),
                isSelectCard: ko.observable(false),
                hasCard: window.checkoutConfig.payment.magenest_stripe.hasCard,
                showPaymentField: ko.observable(false)
            },
            messageContainer: messageContainer,

            initObservable: function () {
                var self = this;
                this._super();
                this.isSelectCard = ko.computed(function () {
                    if ((typeof self.cardId() !== 'undefined')&&(self.hasCard)){
                        return true;
                    }else{
                        return false;
                    }
                }, this);
                this.showPaymentField = ko.computed(function () {
                    if((this.saveCardConfig === "0") || !this.isSelectCard()){
                        return true;
                    }
                }, this);
                return this;
            },

            initialize: function () {
                this._super();
                if (typeof Stripe === "undefined")
                {
                    var script = document.createElement('script');
                    script.onload = function() {
                        Stripe.setPublishableKey(window.checkoutConfig.payment.magenest_stripe_config.publishableKey);
                    };
                    script.onerror = function(response) {
                        console.log("stripe js v2 load error");
                        console.log(response);
                    };
                    script.src = "https://js.stripe.com/v2/";
                    document.head.appendChild(script);
                }
                else {
                    Stripe.setPublishableKey(window.checkoutConfig.payment.magenest_stripe_config.publishableKey);
                }
            },

            placeOrder: function(data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self = this;
                if (this.validate() && additionalValidators.validate()) {
                    self.isPlaceOrderActionAllowed(false);
                    if (!self.isSelectCard()) {
                        var firstName = quote.billingAddress().firstname;
                        var lastName = quote.billingAddress().lastname;
                        var address = quote.billingAddress();
                        var owner = {
                            address: {
                                postal_code: address.postcode,
                                city: address.city,
                                country: address.countryId,
                                line1: address.street[0],
                                line2: address.street[1],
                                state: address.region
                            },
                            name: firstName + " " + lastName,
                            email: (customer.customerData.email === null) ? quote.guestEmail : customer.customerData.email
                        };

                        if (address.telephone) {
                            owner.phone = address.telephone;
                        }

                        Stripe.source.create({
                            type: 'card',
                            card: {
                                number: $('#magenest_stripe_cc_number').val(),
                                cvc: $('#magenest_stripe_cc_cid').val(),
                                exp_month: $('#magenest_stripe_expiration').val(),
                                exp_year: $('#magenest_stripe_expiration_yr').val()
                            },
                            owner: owner
                        }, function (status, response) {
                            if (response.error) {
                                console.log(response);
                                self.messageContainer.addErrorMessage({
                                    message: response.error.message
                                });
                                self.isPlaceOrderActionAllowed(true);
                            } else {
                                self.api_response = response;
                                self.realPlaceOrder();
                            }
                        });

                    }
                    else {
                        self.realPlaceOrder();
                    }
                    return true;
                }
                return false;
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'saved': this.saveCardOption,
                        "stripe_response": JSON.stringify(this.api_response),
                        'cardId': this.cardId()
                    }
                }
            },

            getCode: function() {
                return 'magenest_stripe';
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                var self = this;
                if(window.checkoutConfig.payment.magenest_stripe_config.publishableKey===""){
                    self.messageContainer.addErrorMessage({
                        message: "Stripe public key error"
                    });
                    return false;
                }
                if (typeof Stripe === "undefined"){
                    self.messageContainer.addErrorMessage({
                        message: "Stripe js load error"
                    });
                    return false;
                }

                if(!this.isSelectCard()) {
                    if (!Stripe.card.validateCardNumber(this.creditCardNumber())) {
                        this.isCardNumberError(true);
                        return false;
                    } else {
                        this.isCardNumberError(false);
                    }
                    if (!Stripe.card.validateExpiry(this.creditCardExpMonth(), this.creditCardExpYear())) {
                        this.isCardExpError(true);
                        return false;
                    } else {
                        this.isCardExpError(false);
                    }
                    if (!Stripe.card.validateCVC(this.creditCardVerificationNumber())) {
                        this.isCardCcvError(true);
                        return false;
                    } else {
                        this.isCardCcvError(false);
                    }
                }

                return true;
            },

            getInstructions: function () {
                return window.checkoutConfig.payment.magenest_stripe.instructions;
            },

            realPlaceOrder: function () {
                var self = this;
                this.getPlaceOrderDeferredObject()
                    .fail(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                        }
                    ).done(
                    function () {
                        self.afterPlaceOrder();

                        if (self.redirectAfterPlaceOrder) {
                            redirectOnSuccessAction.execute();
                        }
                    }
                );
            },
            
            afterPlaceOrder: function () {
                var self = this;
                $.post(
                    url.build("stripe/checkout/threedSecure"),
                    {
                        form_key: window.checkoutConfig.formKey
                    },
                    function (response) {
                        if (response.success) {
                            if(response.defaultPay){
                                redirectOnSuccessAction.execute();
                            }
                            if(response.threeDSercueActive){
                                window.location = response.threeDSercueUrl;
                            }

                        }
                        if (response.error){
                            self.isPlaceOrderActionAllowed(true);
                            console.log(response);
                            self.messageContainer.addErrorMessage({
                                message: response.message
                            });
                        }
                    },
                    "json"
                );
            }
        });

    }
);
