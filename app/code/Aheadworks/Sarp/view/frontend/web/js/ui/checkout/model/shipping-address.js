/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Magento_Checkout/js/model/new-customer-address'
    ],
    function(ko, cart, address) {
        'use strict';

        return {
            address: ko.observable(address(cart.getAddressByType('shipping')))
        };
    }
);
