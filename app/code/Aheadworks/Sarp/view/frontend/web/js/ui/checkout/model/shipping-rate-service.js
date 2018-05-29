/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-address',
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-rate-processor/new-address',
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-rate-processor/customer-address'
    ],
    function (shippingAddress, defaultProcessor, customerAddressProcessor) {
        'use strict';

        var processors = {
            'default': defaultProcessor,
            'customer-address': customerAddressProcessor
        };

        /**
         * Process shipping address
         */
        function processShippingAddress() {
            var address = shippingAddress.address(),
                type = address.getType();

            if (processors[type]) {
                processors[type].getRates(address);
            } else {
                processors.default.getRates(address);
            }
        }

        shippingAddress.address.subscribe(processShippingAddress);
        processShippingAddress();

        return {
            /**
             * Register shipping rate processor
             *
             * @param {String} type
             * @param {Object} processor
             */
            registerProcessor: function (type, processor) {
                processors[type] = processor;
            }
        }
    }
);
