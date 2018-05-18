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
        'mage/url'
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

        return Component.extend({
            defaults: {
                template: 'Magenest_Stripe/payment/stripe-payments-alipay',
                redirectAfterPlaceOrder: true
            },
            messageContainer: messageContainer,

            initialize: function () {
                var self = this;
                this._super();
            },

            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self = this;
                if (this.validate() && additionalValidators.validate()) {
                    fullScreenLoader.startLoader();
                    self.isPlaceOrderActionAllowed(false);
                    setBillingAddressAction();
                    setPaymentInformationAction(
                        self.messageContainer,
                        {
                            method: this.getCode()
                        }
                    );
                    $.post(
                        url.build('stripe/checkout/alipaySource'), {
                            form_key: window.checkoutConfig.formKey,
                            billingAddress: ko.toJSON(quote.billingAddress()),
                            shippingAddress: ko.toJSON(quote.shippingAddress()),
                            guestEmail: quote.guestEmail
                        }).done(function (response) {
                        fullScreenLoader.stopLoader(true);
                        self.isPlaceOrderActionAllowed(true);
                        if (response.success) {
                            $.mage.redirect(response.redirect_url);
                        }
                        if (response.error) {
                            self.messageContainer.addErrorMessage({
                                message: "Payment error"
                            });
                            console.log(response);
                        }
                    }).fail(function () {
                            fullScreenLoader.stopLoader(true);
                            self.isPlaceOrderActionAllowed(true);
                            self.messageContainer.addErrorMessage({
                                message: "Payment error"
                            });

                        }
                    );
                    return true;
                }
                return false;
            }
        });
    }
);