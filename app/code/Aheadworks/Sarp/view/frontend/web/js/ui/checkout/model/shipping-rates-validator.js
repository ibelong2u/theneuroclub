/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'ko',
        './shipping-rates-validation-rules',
        '../model/address-converter',
        '../action/select-shipping-address',
        'Magento_Checkout/js/model/postcode-validator',
        'mage/translate',
        'uiRegistry',
        'Magento_OfflineShipping/js/model/shipping-rates-validator/flatrate',
        'Magento_OfflineShipping/js/model/shipping-rates-validator/freeshipping',
        'Magento_OfflineShipping/js/model/shipping-rates-validator/tablerate',
        'Magento_Fedex/js/model/shipping-rates-validator',
        'Magento_Ups/js/model/shipping-rates-validator',
        'Magento_Usps/js/model/shipping-rates-validator',
        'Magento_Dhl/js/model/shipping-rates-validator'
    ],
    function (
        $,
        ko,
        shippingRatesValidationRules,
        addressConverter,
        selectShippingAddress,
        postcodeValidator,
        $t,
        uiRegistry,
        validatorFlatRate,
        validatorFreeShipping,
        validatorTableRate,
        validatorFedex,
        validatorUps,
        validatorUsps,
        validatorDhl
    ) {
        'use strict';

        var checkoutConfig = window.awSarpCheckoutConfig,
            validators = [],
            observedElements = [],
            postcodeElement = null,
            postcodeElementName = 'postcode';

        var shippingRateValidator = {
            validateAddressTimeout: 0,
            validateDelay: 2000,

            /**
             * Register validator
             *
             * @param {String} carrier
             * @param {Object} validator
             */
            registerValidator: function (carrier, validator) {
                if (checkoutConfig.activeCarriers.indexOf(carrier) !== -1) {
                    validators.push(validator);
                }

                return this;
            },

            /**
             * Validate address data
             *
             * @param {Object} address
             * @return {Boolean}
             */
            validateAddressData: function (address) {
                return validators.some(function (validator) {
                    return validator.validate(address);
                });
            },

            /**
             * Perform postponed binding for fieldset elements
             *
             * @param {String} formPath
             */
            initFields: function (formPath) {
                var self = this,
                    elements = shippingRatesValidationRules.getObservableFields();

                if ($.inArray(postcodeElementName, elements) === -1) {
                    // Add postcode field to observables if not exist for zip code validation support
                    elements.push(postcodeElementName);
                }

                $.each(elements, function (index, field) {
                    uiRegistry.async(formPath + '.' + field)(self.doElementBinding.bind(self));
                });
            },

            /**
             * Bind shipping rates request to form element
             *
             * @param {Object} element
             * @param {Boolean} force
             * @param {Number} delay
             */
            doElementBinding: function (element, force, delay) {
                var observableFields = shippingRatesValidationRules.getObservableFields();

                if (element && (observableFields.indexOf(element.index) !== -1 || force)) {
                    if (element.index !== postcodeElementName) {
                        this.bindHandler(element, delay);
                    }
                }

                if (element.index === postcodeElementName) {
                    this.bindHandler(element, delay);
                    postcodeElement = element;
                }
            },

            /**
             * @param {*} elements
             * @param {Boolean} force
             * @param {Number} delay
             */
            bindChangeHandlers: function (elements, force, delay) {
                var self = this;

                $.each(elements, function (index, elem) {
                    self.doElementBinding(elem, force, delay);
                });
            },

            /**
             * @param {Object} element
             * @param {Number} delay
             */
            bindHandler: function (element, delay) {
                var self = this;

                delay = typeof delay === 'undefined' ? self.validateDelay : delay;

                if (element.component.indexOf('/group') !== -1) {
                    $.each(element.elems(), function (index, elem) {
                        self.bindHandler(elem);
                    });
                } else {
                    element.on('value', function () {
                        clearTimeout(self.validateAddressTimeout);
                        self.validateAddressTimeout = setTimeout(function () {
                            self.postcodeValidation();
                            self.validateFields();
                        }, delay);
                    });
                    observedElements.push(element);
                }
            },

            /**
             * @return {*}
             */
            postcodeValidation: function () {
                var countryId = $('select[name="country_id"]').val(),
                    validationResult,
                    warnMessage;

                if (postcodeElement == null || postcodeElement.value() == null) {
                    return true;
                }

                postcodeElement.warn(null);
                validationResult = postcodeValidator.validate(postcodeElement.value(), countryId);

                if (!validationResult) {
                    warnMessage = $t('Provided Zip/Postal Code seems to be invalid.');

                    if (postcodeValidator.validatedPostCodeExample.length) {
                        warnMessage += $t(' Example: ') + postcodeValidator.validatedPostCodeExample.join('; ') + '. ';
                    }
                    warnMessage += $t('If you believe it is the right one you can ignore this notice.');
                    postcodeElement.warn(warnMessage);
                }

                return validationResult;
            },

            /**
             * Convert form data to cart address and validate fields for shipping rates
             */
            validateFields: function () {
                var addressFlat = addressConverter.formDataProviderToFlatData(
                        this.collectObservedData(),
                        'shippingAddress'
                    ),
                    address;

                if (this.validateAddressData(addressFlat)) {
                    addressFlat = uiRegistry.get('checkoutProvider').shippingAddress;
                    address = addressConverter.formAddressDataToCartAddress(addressFlat);
                    selectShippingAddress(address);
                }
            },

            /**
             * Collect observed fields data to object
             *
             * @returns {*}
             */
            collectObservedData: function () {
                var observedValues = {};

                $.each(observedElements, function (index, field) {
                    observedValues[field.dataScope] = field.value();
                });

                return observedValues;
            }
        };

        shippingRateValidator
            .registerValidator('flatrate', validatorFlatRate)
            .registerValidator('freeshipping', validatorFreeShipping)
            .registerValidator('tablerate', validatorTableRate)
            .registerValidator('fedex', validatorFedex)
            .registerValidator('ups', validatorUps)
            .registerValidator('usps', validatorUsps)
            .registerValidator('dhl', validatorDhl);

        return shippingRateValidator;
    }
);
