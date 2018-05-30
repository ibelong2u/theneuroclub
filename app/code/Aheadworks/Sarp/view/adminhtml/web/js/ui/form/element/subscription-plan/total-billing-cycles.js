/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'Magento_Ui/js/form/element/abstract',
    'mage/translate'
], function (Abstract, $t) {
    'use strict';

    return Abstract.extend({
        defaults: {
            isInputMode: true,
            isInputModePerEngine: {},
            defaultNotice: $t('Leave 0 or empty for infinite subscription'),
            replaceText: $t('Infinite'),
            engineCode: 'paypal',
            listens: {
                engineCode: 'processEngineCode'
            }
        },

        /**
         * @inheritdoc
         */
        initConfig: function (config) {
            this._super()
                .initIsInputModeValue(config)
                .initNotice(config);

            return this;
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super()
                .observe([
                    'isInputMode',
                    'engineCode',
                    'notice'
                ]);

            return this;
        },

        /**
         * Init input mode flag value
         *
         * @param {Object} config
         * @returns {Object}
         */
        initIsInputModeValue: function (config) {
            this.isInputMode = config.isInputModePerEngine[this.engineCode];

            return this;
        },

        /**
         * Init notice
         *
         * @param {Object} config
         * @returns {Object}
         */
        initNotice: function (config) {
            this.notice = this.isInputMode ? config.defaultNotice : '';

            return this;
        },

        /**
         * Process engine code value change
         *
         * @param {string} engineCode
         */
        processEngineCode: function (engineCode) {
            var isInputMode = this.isInputModePerEngine[engineCode],
                notice = isInputMode ? this.defaultNotice : '';

            this.isInputMode(isInputMode);
            this.notice(notice);
            if (!isInputMode) {
                this.value('');
            }
        }
    });
});
