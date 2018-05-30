/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-save-processor/default'
    ],
    function(defaultProcessor) {
        'use strict';

        var processors = {
            'default': defaultProcessor
        };

        return {
            /**
             * Register shipping save processor
             *
             * @param {String} type
             * @param {Object} processor
             */
            registerProcessor: function(type, processor) {
                processors[type] = processor;
            },

            /**
             * Save shipping information
             *
             * @param {String} type
             * @returns {Array}
             */
            saveShippingInformation: function (type) {
                return processors[type]
                    ? processors[type].saveShippingInformation()
                    : processors['default'].saveShippingInformation();
            }
        }
    }
);
