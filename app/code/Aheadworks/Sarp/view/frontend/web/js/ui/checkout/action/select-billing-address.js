/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'Aheadworks_Sarp/js/ui/checkout/model/billing-address',
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-address'
    ],
    function ($, billingAddressModel, shippingAddressModel) {
        'use strict';

        return function (billingAddress) {
            var address = null;

            if (shippingAddressModel.address()
                && billingAddress.getCacheKey() == shippingAddressModel.address().getCacheKey()
            ) {
                address = $.extend({}, billingAddress);
                address.saveInAddressBook = null;
            } else {
                address = billingAddress;
            }
            billingAddressModel.address(address);
        };
    }
);
