/**
 * Created by joel on 31/12/2016.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/model/messages',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/action/set-billing-address',
        'mage/url',
        'https://js.stripe.com/v2/'
    ],
    function ($,
              ko,
              Component,
              setPaymentInformationAction,
              fullScreenLoadern,
              checkoutData,
              quote,
              fullScreenLoader,
              redirectOnSuccessAction,
              additionalValidators,
              messageContainer,
              customer,
              setBillingAddressAction,
              url
    ) {
        'use strict';

        var quoteData = window.checkoutConfig.quoteData;
        var amount = 0;
        return Component.extend({
            defaults: {
                template: 'Magenest_Stripe/payment/stripe-payments-giropay',
                redirectAfterPlaceOrder: false
            },
            messageContainer: messageContainer,

            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self = this;
                if (this.validate() && additionalValidators.validate()) {
                    try {
                        fullScreenLoader.startLoader();
                        self.isPlaceOrderActionAllowed(false);
                        setBillingAddressAction();
                        setPaymentInformationAction(
                            self.messageContainer,
                            {
                                method: this.getCode()
                            }
                        );
                        $.ajax({
                            url: url.build('stripe/checkout/giroPayConfig'),
                            dataType: "json",
                            data: {
                                form_key: window.checkoutConfig.formKey,
                                billingAddress: ko.toJSON(quote.billingAddress()),
                                shippingAddress: ko.toJSON(quote.shippingAddress()),
                                guestEmail: quote.guestEmail
                            },
                            type: 'POST',
                            success: function (response) {
                                if (!response.errback) {
                                    amount = response.amount;
                                }
                                var firstName = quote.billingAddress().firstname;
                                var lastName = quote.billingAddress().lastname;
                                if (amount === 0) {
                                    fullScreenLoader.stopLoader(true);
                                    self.isPlaceOrderActionAllowed(true);
                                    self.messageContainer.addErrorMessage({
                                        message: 'Unable to get amount, please try again.'
                                    });
                                }
                                else {
                                    var address = quote.billingAddress();
                                    Stripe.source.create({
                                        type: 'giropay',
                                        amount: Math.round(self.getAmount(amount)),
                                        currency: quoteData.base_currency_code,
                                        owner: {
                                            address: {
                                                postal_code: address.postcode,
                                                city: address.city,
                                                country: address.countryId,
                                                line1: address.street[0],
                                                line2: (address.street[1] === 'undefined') ? address.street[1] : '',
                                                state: address.region
                                            },
                                            name: firstName + " " + lastName,
                                            email: (customer.customerData.email == null) ? quote.guestEmail : customer.customerData.email,
                                            phone: address.telephone
                                        },
                                        redirect: {
                                            return_url: url.build('stripe/checkout/giroPayCharge')
                                        }
                                    }, function (status, response) {
                                        if (response.error) {
                                            self.messageContainer.addErrorMessage({
                                                message: response.error.message
                                            });
                                            fullScreenLoader.stopLoader(true);
                                            self.isPlaceOrderActionAllowed(true);
                                        } else {
                                            window.location.href = response.redirect.url;
                                        }
                                    });

                                }
                            },
                            error: function () {
                                fullScreenLoader.stopLoader(true);
                                self.isPlaceOrderActionAllowed(true);
                                self.messageContainer.addErrorMessage({
                                    message: 'Something went wrong, please try again.'
                                });
                            }
                        });
                    }
                    catch (err) {
                        fullScreenLoader.stopLoader(true);
                        self.isPlaceOrderActionAllowed(true);
                        self.messageContainer.addErrorMessage({
                            message: 'Something went wrong, please try again.'
                        });
                    }
                }
            },

            getAmount: function (amount) {
                var multiply = 100;
                return amount * multiply;
            },

            isActive: function () {
                return quoteData.base_currency_code.toLowerCase() === 'eur';
            }
        });
    }
);