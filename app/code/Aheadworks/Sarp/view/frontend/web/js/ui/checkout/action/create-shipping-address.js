/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Magento_Customer/js/model/address-list',
        'Aheadworks_Sarp/js/ui/checkout/model/address-converter'
    ],
    function(addressList, addressConverter) {
        'use strict';

        return function(addressData) {
            var address = addressConverter.formAddressDataToCartAddress(addressData),
                isAddressUpdated = addressList().some(function(currentAddress, index, addresses) {
                if (currentAddress.getKey() == address.getKey()) {
                    addresses[index] = address;
                    return true;
                }
                return false;
            });

            if (!isAddressUpdated) {
                addressList.push(address);
            } else {
                addressList.valueHasMutated();
            }
            return address;
        };
    }
);
