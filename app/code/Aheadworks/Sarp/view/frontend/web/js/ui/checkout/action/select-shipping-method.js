/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Aheadworks_Sarp/js/ui/cart/model/cart'
    ],
    function (cart) {
        'use strict';

        return function (shippingMethod) {
            cart.shippingMethod(shippingMethod)
        }
    }
);
