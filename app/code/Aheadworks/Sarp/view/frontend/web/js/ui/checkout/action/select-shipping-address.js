/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-address'
    ],
    function(shippingAddress) {
        'use strict';

        return function(address) {
            shippingAddress.address(address);
        };
    }
);
