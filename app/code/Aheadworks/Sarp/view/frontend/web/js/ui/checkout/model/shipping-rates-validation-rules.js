/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'Magento_OfflineShipping/js/model/shipping-rates-validation-rules/flatrate',
        'Magento_OfflineShipping/js/model/shipping-rates-validation-rules/freeshipping',
        'Magento_OfflineShipping/js/model/shipping-rates-validation-rules/tablerate',
        'Magento_Fedex/js/model/shipping-rates-validation-rules',
        'Magento_Ups/js/model/shipping-rates-validation-rules',
        'Magento_Usps/js/model/shipping-rates-validation-rules',
        'Magento_Dhl/js/model/shipping-rates-validation-rules'
    ],
    function (
        $,
        ruleFlatRate,
        ruleFreeShipping,
        ruleFreeTableRate,
        ruleFedex,
        ruleUps,
        ruleUsps,
        ruleDhl
    ) {
        'use strict';

        var ratesRules = {},
            checkoutConfig = window.awSarpCheckoutConfig;

        var shippingRatesValidatorRules = {

            /**
             * Register rules
             *
             * @param {String} carrier
             * @param {Object} rules
             * @returns {shippingRatesValidatorRules}
             */
            registerRules: function (carrier, rules) {
                if (checkoutConfig.activeCarriers.indexOf(carrier) !== -1) {
                    ratesRules[carrier] = rules.getRules();
                }
                return this;
            },

            /**
             * Get rules
             *
             * @returns {Object}
             */
            getRules: function () {
                return ratesRules;
            },

            /**
             * Get observable fields
             *
             * @returns {Array}
             */
            getObservableFields: function () {
                var self = this,
                    observableFields = [];

                $.each(self.getRules(), function (carrier, fields) {
                    $.each(fields, function (field, rules) {
                        if (observableFields.indexOf(field) === -1) {
                            observableFields.push(field);
                        }
                    });
                });

                return observableFields;
            }
        };

        shippingRatesValidatorRules
            .registerRules('flatrate', ruleFlatRate)
            .registerRules('freeshipping', ruleFreeShipping)
            .registerRules('tablerate', ruleFreeTableRate)
            .registerRules('fedex', ruleFedex)
            .registerRules('ups', ruleUps)
            .registerRules('usps', ruleUsps)
            .registerRules('dhl', ruleDhl);

        return shippingRatesValidatorRules;
    }
);
