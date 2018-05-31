/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'Aheadworks_Sarp/js/ui/checkout/view/payment/cc-form',
        'adyen/encrypt'
    ],
    function ($, Component, encrypt) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_Sarp/checkout/payment/method-renderer/adyen/cc',
                timeoutMessage: 'Sorry, but something went wrong. Please contact the seller.',
                formSelector: '[data-role=adyen-cc-form]',
                creditCardOwner: ''
            },

            /**
             * @inheritdoc
             */
            initObservable: function () {
                this._super()
                    .observe(['creditCardOwner']);

                return this;
            },

            /**
             * @inheritdoc
             */
            getData: function () {
                return {
                    'method_code': this.item.method,
                    'payment_data': {
                        'cc_type': this.creditCardType(),
                        'encrypted_data': this._encryptCreditCardData(),
                        'generationtime': this.getGenerationTime()
                    }
                };
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
            getCode: function () {
                return 'adyen_cc';
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
             * Encrypt credit card data
             *
             * @returns {string}
             */
            _encryptCreditCardData: function () {
                var encryption = encrypt.createEncryption(this.getCseKey()),
                    dataToEncrypt = {
                        number : this.creditCardNumber(),
                        cvc : this.creditCardVerificationNumber(),
                        holderName : this.creditCardOwner(),
                        expiryMonth : this.creditCardExpMonth(),
                        expiryYear : this.creditCardExpYear(),
                        generationtime : this.getGenerationTime()
                    };

                return encryption.encrypt(dataToEncrypt);
            },

            /**
             * Get Cse key
             *
             * @returns {string}
             */
            getCseKey: function () {
                return window.checkoutConfig.payment.ccform.cseKey[this.getCode()];
            },

            /**
             * Get generation time
             *
             * @returns {string}
             */
            getGenerationTime: function () {
                return window.checkoutConfig.payment.ccform.generationTime[this.getCode()];
            }
        });
    }
);
