/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Aheadworks_Sarp/js/ui/checkout/model/address-converter'
    ],
    function (addressConverter) {
        'use strict';

        return function (addressData) {
            return addressConverter.formAddressDataToCartAddress(addressData);
        };
    }
);
