/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (SingleCheckbox) {
    'use strict';

    return SingleCheckbox.extend({
        defaults: {
            engineCodeToAvailableMap: {},
            engineCode: 'paypal',
            listens: {
                'engineCode': 'processEngineCode'
            }
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super()
                .observe(['engineCode']);

            return this;
        },

        /**
         * @inheritdoc
         */
        setInitialValue: function () {
            this._super()
                .initAvailability();

            return this;
        },

        /**
         * Init availability
         */
        initAvailability: function () {
            this._applyAvailability(this.engineCode());
        },

        /**
         * Apply availability for engine code
         *
         * @param {string} engineCode
         * @private
         */
        _applyAvailability: function (engineCode) {
            var isAvailable = this._isAvailableByEngineCode(engineCode),
                value = isAvailable ? this.initialValue : '';

            this.visible(isAvailable);
            this.value(value);
        },

        /**
         * Check if available by engine code
         *
         * @param {string} engineCode
         * @returns {boolean}
         * @private
         */
        _isAvailableByEngineCode: function (engineCode) {
            return engineCode in this.engineCodeToAvailableMap
                ? this.engineCodeToAvailableMap[engineCode]
                : false;
        },

        /**
         * Process engine code value change
         *
         * @param {string} engineCode
         */
        processEngineCode: function (engineCode) {
            this._applyAvailability(engineCode);
        }
    });
});
