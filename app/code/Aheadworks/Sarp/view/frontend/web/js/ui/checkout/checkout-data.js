/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, storage) {
    'use strict';

    var cacheKey = 'aw-sarp-checkout-data';

    /**
     * Get stored data
     *
     * @returns {Object}
     */
    var getData = function () {
        return storage.get(cacheKey)();
    };

    /**
     * Save data
     *
     * @param {Object} checkoutData
     */
    var saveData = function (checkoutData) {
        storage.set(cacheKey, checkoutData);
    };

    if ($.isEmptyObject(getData())) {
        saveData({
            'selectedShippingAddress': null,
            'shippingAddressFromData': null,
            'newCustomerShippingAddress': null,
            'selectedShippingRate': null,
            'selectedPaymentMethod': null,
            'selectedBillingAddress': null,
            'billingAddressFormData': null,
            'newCustomerBillingAddress': null
        });
    }

    return {
        /**
         * Set selected shipping address
         *
         * @param data
         */
        setSelectedShippingAddress: function (data) {
            var obj = getData();
            obj.selectedShippingAddress = data;
            saveData(obj);
        },

        /**
         * Get selected shipping address
         *
         * @returns {null|*}
         */
        getSelectedShippingAddress: function () {
            return getData().selectedShippingAddress;
        },

        /**
         * Set shipping address from data
         *
         * @param data
         */
        setShippingAddressFromData: function (data) {
            var obj = getData();
            obj.shippingAddressFromData = data;
            saveData(obj);
        },

        /**
         * Get shipping address from data
         *
         * @returns {null|*}
         */
        getShippingAddressFromData: function () {
            return getData().shippingAddressFromData;
        },

        /**
         * Set new customer shipping address
         *
         * @param data
         */
        setNewCustomerShippingAddress: function (data) {
            var obj = getData();
            obj.newCustomerShippingAddress = data;
            saveData(obj);
        },

        /**
         * Get new customer shipping address
         *
         * @returns {null|*}
         */
        getNewCustomerShippingAddress: function () {
            return getData().newCustomerShippingAddress;
        },

        /**
         * Set selected shipping rate
         *
         * @param data
         */
        setSelectedShippingRate: function (data) {
            var obj = getData();
            obj.selectedShippingRate = data;
            saveData(obj);
        },

        /**
         * Get selected shipping rate
         *
         * @returns {null|*}
         */
        getSelectedShippingRate: function() {
            return getData().selectedShippingRate;
        },

        /**
         * Set selected payment method
         *
         * @param data
         */
        setSelectedPaymentMethod: function (data) {
            var obj = getData();
            obj.selectedPaymentMethod = data;
            saveData(obj);
        },

        /**
         * Get selected payment method
         *
         * @returns {null|*}
         */
        getSelectedPaymentMethod: function() {
            return getData().selectedPaymentMethod;
        },

        /**
         * Set selected billing address
         *
         * @param data
         */
        setSelectedBillingAddress: function (data) {
            var obj = getData();
            obj.selectedBillingAddress = data;
            saveData(obj);
        },

        /**
         * Get selected billing address
         *
         * @returns {null|*}
         */
        getSelectedBillingAddress: function () {
            return getData().selectedBillingAddress;
        },

        /**
         * Set billing address from data
         *
         * @param data
         */
        setBillingAddressFromData: function (data) {
            var obj = getData();
            obj.billingAddressFromData = data;
            saveData(obj);
        },

        /**
         * Get billing address from data
         *
         * @returns {*}
         */
        getBillingAddressFromData: function () {
            return getData().billingAddressFromData;
        },

        /**
         * Set new customer billing address
         *
         * @param data
         */
        setNewCustomerBillingAddress: function (data) {
            var obj = getData();
            obj.newCustomerBillingAddress = data;
            saveData(obj);
        },

        /**
         * Get new customer billing address
         *
         * @returns {null|*}
         */
        getNewCustomerBillingAddress: function () {
            return getData().newCustomerBillingAddress;
        },

        /**
         * Set validated email value
         *
         * @param {string} email
         */
        setValidatedEmailValue: function (email) {
            var obj = getData();
            obj.validatedEmailValue = email;
            saveData(obj);
        },

        /**
         * Get validated email value
         *
         * @returns {string}
         */
        getValidatedEmailValue: function () {
            var obj = getData();
            return (obj.validatedEmailValue) ? obj.validatedEmailValue : '';
        },

        /**
         * Set input field email value
         *
         * @param {string} email
         */
        setInputFieldEmailValue: function (email) {
            var obj = getData();
            obj.inputFieldEmailValue = email;
            saveData(obj);
        },

        /**
         * Get input field email value
         *
         * @returns {string}
         */
        getInputFieldEmailValue: function () {
            var obj = getData();
            return (obj.inputFieldEmailValue) ? obj.inputFieldEmailValue : '';
        }
    }
});
