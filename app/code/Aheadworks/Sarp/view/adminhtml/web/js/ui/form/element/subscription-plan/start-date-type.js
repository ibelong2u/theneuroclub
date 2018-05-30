/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            options: [],
            optionsPerEngine: {},
            showDayOfMonthInputSwitchValue: 1,
            dayOfMonthInputVisible: false,
            dayOfMonthValue: '',
            engineCode: 'paypal',
            listens: {
                value: 'processValue',
                engineCode: 'processEngineCode'
            }
        },

        /**
         * @inheritdoc
         */
        initConfig: function (config) {
            this._super()
                .initOptions(config);

            return this;
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super()
                .observe([
                    'options',
                    'dayOfMonthInputVisible',
                    'dayOfMonthValue',
                    'engineCode'
                ]);

            return this;
        },

        /**
         * Init options
         *
         * @param {Object} config
         * @returns {Object}
         */
        initOptions: function (config) {
            this.options = config.optionsPerEngine[this.engineCode];

            return this;
        },

        /**
         * Process value change
         *
         * @param {integer} value
         */
        processValue: function (value) {
            var isShowDayOfMonthInput = (value == this.showDayOfMonthInputSwitchValue);

            this.dayOfMonthInputVisible(isShowDayOfMonthInput);
            if (!isShowDayOfMonthInput) {
                this.dayOfMonthValue('');
            }
        },

        /**
         * Process engine code value change
         *
         * @param {string} engineCode
         */
        processEngineCode: function (engineCode) {
            this.options(this.optionsPerEngine[engineCode]);
        }
    });
});
