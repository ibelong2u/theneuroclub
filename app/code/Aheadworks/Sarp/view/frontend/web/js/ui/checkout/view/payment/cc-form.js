/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'underscore',
        'mage/translate',
        'Aheadworks_Sarp/js/ui/checkout/view/payment/default',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (
        _,
        $t,
        Component,
        creditCardData,
        cardNumberValidator
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: '',
                creditCardSsStartMonth: '',
                creditCardSsStartYear: '',
                creditCardSsIssue: '',
                creditCardVerificationNumber: '',
                selectedCardType: null,
                listens: {
                    creditCardNumber: 'onCCNumberChange',
                    creditCardExpYear: 'onCCExpYearChange',
                    creditCardExpMonth: 'onCCExpMonthChange',
                    creditCardVerificationNumber: 'onCCVerificationNumberChange'
                }
            },

            /**
             * @inheritdoc
             */
            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
                        'creditCardSsStartMonth',
                        'creditCardSsStartYear',
                        'creditCardSsIssue',
                        'selectedCardType'
                    ]);

                return this;
            },

            /**
             * Cart number change handler
             *
             * @param {String} value
             */
            onCCNumberChange: function (value) {
                var result;

                this.selectedCardType(null);

                if (value === '' || value === null) {
                    return false;
                }
                result = cardNumberValidator(value);

                if (!result.isPotentiallyValid && !result.isValid) {
                    return false;
                }
                if (result.card !== null) {
                    this.selectedCardType(result.card.type);
                    creditCardData.creditCard = result.card;
                }
                if (result.isValid) {
                    creditCardData.creditCardNumber = value;
                    this.creditCardType(result.card.type);
                }
            },

            /**
             * Expiration year change handler
             *
             * @param {String} value
             */
            onCCExpYearChange: function (value) {
                creditCardData.expirationYear = value;
            },

            /**
             * Expiration month change handler
             *
             * @param {String} value
             */
            onCCExpMonthChange: function (value) {
                creditCardData.expirationMonth = value;
            },

            /**
             * Verification number change handler
             *
             * @param {String} value
             */
            onCCVerificationNumberChange: function (value) {
                creditCardData.cvvCode = value;
            },

            /**
             * @inheritdoc
             */
            getCode: function () {
                return 'cc';
            },

            /**
             * @inheritdoc
             */
            getData: function () {
                return {
                    'method_code': this.item.method,
                    'payment_data': {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_ss_start_month': this.creditCardSsStartMonth(),
                        'cc_ss_start_year': this.creditCardSsStartYear(),
                        'cc_ss_issue': this.creditCardSsIssue(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber()
                    }
                };
            },

            /**
             * Get list of available credit card types
             *
             * @returns {Object}
             */
            getCcAvailableTypes: function () {
                return window.checkoutConfig.payment.ccform.availableTypes[this.getCode()];
            },

            /**
             * Get credit card icons
             *
             * @param {String} type
             * @returns {Boolean}
             */
            getIcons: function (type) {
                return window.checkoutConfig.payment.ccform.icons.hasOwnProperty(type) ?
                    window.checkoutConfig.payment.ccform.icons[type]
                    : false;
            },

            /**
             * Get list of months
             *
             * @returns {Object}
             */
            getCcMonths: function () {
                return window.checkoutConfig.payment.ccform.months[this.getCode()];
            },

            /**
             * Get list of years
             *
             * @returns {Object}
             */
            getCcYears: function () {
                return window.checkoutConfig.payment.ccform.years[this.getCode()];
            },

            /**
             * Check if payment has verification
             *
             * @returns {Boolean}
             */
            hasVerification: function () {
                return window.checkoutConfig.payment.ccform.hasVerification[this.getCode()];
            },

            /**
             * Get image url for CVV
             *
             * @returns {String}
             */
            getCvvImageUrl: function () {
                return window.checkoutConfig.payment.ccform.cvvImageUrl[this.getCode()];
            },

            /**
             * Get image for CVV html
             *
             * @returns {String}
             */
            getCvvImageHtml: function () {
                return '<img src="' + this.getCvvImageUrl() +
                    '" alt="' + $t('Card Verification Number Visual Reference') +
                    '" title="' + $t('Card Verification Number Visual Reference') +
                    '" />';
            },

            /**
             * Get list of available credit card types values
             *
             * @returns {Object}
             */
            getCcAvailableTypesValues: function () {
                return _.map(this.getCcAvailableTypes(), function (value, key) {
                    return {
                        'value': key,
                        'type': value
                    };
                });
            },

            /**
             * Get list of available month values
             *
             * @returns {Object}
             */
            getCcMonthsValues: function () {
                return _.map(this.getCcMonths(), function (value, key) {
                    return {
                        'value': key,
                        'month': value
                    };
                });
            },

            /**
             * Get list of available year values
             *
             * @returns {Object}
             */
            getCcYearsValues: function () {
                return _.map(this.getCcYears(), function (value, key) {
                    return {
                        'value': key,
                        'year': value
                    };
                });
            },

            /**
             * Check if show legend
             *
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return false;
            },

            /**
             * Get available credit card type by code
             *
             * @param {String} code
             * @returns {String}
             */
            getCcTypeTitleByCode: function (code) {
                var title = '',
                    keyValue = 'value',
                    keyType = 'type';

                _.each(this.getCcAvailableTypesValues(), function (value) {
                    if (value[keyValue] === code) {
                        title = value[keyType];
                    }
                });

                return title;
            },

            /**
             * Prepare credit card number to output
             *
             * @param {String} number
             * @returns {String}
             */
            formatDisplayCcNumber: function (number) {
                return 'xxxx-' + number.substr(-4);
            },

            /**
             * Get credit card details
             *
             * @returns {Array}
             */
            getInfo: function () {
                return [
                    {
                        'name': 'Credit Card Type', value: this.getCcTypeTitleByCode(this.creditCardType())
                    },
                    {
                        'name': 'Credit Card Number', value: this.formatDisplayCcNumber(this.creditCardNumber())
                    }
                ];
            }
        });
    }
);
