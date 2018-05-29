/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko',
        'Aheadworks_Sarp/js/ui/checkout/model/checkout-data-resolver'
    ],
    function (ko, checkoutDataResolver) {
        'use strict';

        var shippingRates = ko.observableArray([]);

        return {
            isLoading: ko.observable(false),

            /**
             * Set shipping rates
             *
             * @param ratesData
             */
            setShippingRates: function(ratesData) {
                shippingRates(ratesData);
                shippingRates.valueHasMutated();
                checkoutDataResolver.resolveShippingRates(ratesData);
            },

            /**
             * Get shipping rates
             *
             * @returns {*}
             */
            getShippingRates: function() {
                return shippingRates;
            }
        };
    }
);
