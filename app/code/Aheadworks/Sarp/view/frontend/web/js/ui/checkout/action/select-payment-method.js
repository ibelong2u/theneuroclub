/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Aheadworks_Sarp/js/ui/checkout/model/payment-method'
    ],
    function(paymentMethod) {
        'use strict';

        return function (method) {
            paymentMethod.method(method);
        }
    }
);
