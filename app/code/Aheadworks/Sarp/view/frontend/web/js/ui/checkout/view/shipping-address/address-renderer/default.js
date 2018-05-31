/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'ko',
    'uiComponent',
    'Aheadworks_Sarp/js/ui/checkout/model/shipping-address',
    'Aheadworks_Sarp/js/ui/checkout/action/select-shipping-address',
    'Aheadworks_Sarp/js/ui/checkout/model/shipping-address/form-popup-state',
    'Aheadworks_Sarp/js/ui/checkout/checkout-data',
    'Magento_Customer/js/customer-data'
], function(
    $,
    ko,
    Component,
    shippingAddressModel,
    selectShippingAddressAction,
    formPopUpState,
    checkoutData,
    customerData
) {
    'use strict';

    var countryData = customerData.get('directory-data');

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-address/address-renderer/default',
            popUpSelector: '[data-open-modal=opc-new-shipping-address]'
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super();
            this.isSelected = ko.computed(function() {
                return shippingAddressModel.address().getKey() == this.address().getKey();
            }, this);

            return this;
        },

        /**
         * Get country name
         *
         * @param {Number} countryId
         * @returns {string}
         */
        getCountryName: function(countryId) {
            return (countryData()[countryId] != undefined) ? countryData()[countryId].name : '';
        },

        /**
         * Set selected customer shipping address handler
         */
        selectAddress: function() {
            selectShippingAddressAction(this.address());
            checkoutData.setSelectedShippingAddress(this.address().getKey());
        },

        /**
         * Edit address handler
         */
        editAddress: function() {
            formPopUpState.isVisible(true);
            this.showPopup();
        },

        /**
         * Show popup
         */
        showPopup: function() {
            $(this.popUpSelector).trigger('click');
        }
    });
});
