/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'underscore',
        'Magento_Customer/js/model/address-list',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-address',
        'Aheadworks_Sarp/js/ui/checkout/model/billing-address',
        'Aheadworks_Sarp/js/ui/checkout/checkout-data',
        'Aheadworks_Sarp/js/ui/checkout/action/create-shipping-address',
        'Aheadworks_Sarp/js/ui/checkout/action/select-shipping-address',
        'Aheadworks_Sarp/js/ui/checkout/action/select-shipping-method',
        'Aheadworks_Sarp/js/ui/checkout/model/payment-service',
        'Aheadworks_Sarp/js/ui/checkout/action/select-payment-method',
        'Aheadworks_Sarp/js/ui/checkout/model/address-converter',
        'Aheadworks_Sarp/js/ui/checkout/action/select-billing-address',
        'Aheadworks_Sarp/js/ui/checkout/action/create-billing-address'
    ],
    function (
        _,
        addressList,
        cart,
        shippingAddressModel,
        billingAddressModel,
        checkoutData,
        createShippingAddressAction,
        selectShippingAddressAction,
        selectShippingMethodAction,
        paymentService,
        selectPaymentMethodAction,
        addressConverter,
        selectBillingAddress,
        createBillingAddress
    ) {
        'use strict';

        return {
            /**
             * Resolve shipping address. Used local storage
             */
            resolveShippingAddress: function () {
                var newCustomerShippingAddress = checkoutData.getNewCustomerShippingAddress();

                if (newCustomerShippingAddress) {
                    createShippingAddressAction(newCustomerShippingAddress);
                }
                this.applyShippingAddress();
            },

            /**
             * Apply resolved address to cart
             */
            applyShippingAddress: function () {
                var address,
                    isShippingAddressInitialized;

                if (addressList().length == 0) {
                    address = addressConverter.formAddressDataToCartAddress(
                        checkoutData.getShippingAddressFromData()
                    );
                    selectShippingAddressAction(address);
                } else {
                    isShippingAddressInitialized = addressList.some(function (addressFromList) {
                        if (checkoutData.getSelectedShippingAddress() == addressFromList.getKey()) {
                            selectShippingAddressAction(addressFromList);

                            return true;
                        }

                        return false;
                    });

                    if (!isShippingAddressInitialized) {
                        isShippingAddressInitialized = addressList.some(function (address) {
                            if (address.isDefaultShipping()) {
                                selectShippingAddressAction(address);

                                return true;
                            }

                            return false;
                        });
                    }

                    if (!isShippingAddressInitialized && addressList().length == 1) {
                        selectShippingAddressAction(addressList()[0]);
                    }
                }
            },

            /**
             * Resolve shipping rates
             *
             * @param {Object} ratesData
             */
            resolveShippingRates: function (ratesData) {
                var selectedShippingRate = checkoutData.getSelectedShippingRate(),
                    availableRate = false;

                if (ratesData.length == 1) {
                    selectShippingMethodAction(ratesData[0]);
                } else {
                    if (cart.shippingMethod()) {
                        availableRate = _.find(ratesData, function (rate) {
                            return rate.carrier_code == cart.shippingMethod().carrier_code &&
                                rate.method_code == cart.shippingMethod().method_code;
                        });
                    }

                    if (!availableRate && selectedShippingRate) {
                        availableRate = _.find(ratesData, function (rate) {
                            return rate.carrier_code + '_' + rate.method_code === selectedShippingRate;
                        });
                    }

                    if (!availableRate) {
                        selectShippingMethodAction(null);
                    } else {
                        selectShippingMethodAction(availableRate);
                    }
                }
            },

            /**
             * Resolve payment method. Used local storage
             */
            resolvePaymentMethod: function () {
                var availablePaymentMethods = paymentService.getAvailablePaymentMethods(),
                    selectedPaymentMethod = checkoutData.getSelectedPaymentMethod();

                if (selectedPaymentMethod) {
                    availablePaymentMethods.some(function (payment) {
                        if (payment.method == selectedPaymentMethod) {
                            selectPaymentMethodAction(payment);
                        }
                    });
                }
            },

            /**
             * Resolve billing address. Used local storage
             */
            resolveBillingAddress: function () {
                var selectedBillingAddress = checkoutData.getSelectedBillingAddress(),
                    newCustomerBillingAddressData = checkoutData.getNewCustomerBillingAddress();

                if (selectedBillingAddress) {
                    if (selectedBillingAddress == 'new-customer-address' && newCustomerBillingAddressData) {
                        selectBillingAddress(createBillingAddress(newCustomerBillingAddressData));
                    } else {
                        addressList.some(function (address) {
                            if (selectedBillingAddress == address.getKey()) {
                                selectBillingAddress(address);
                            }
                        });
                    }
                } else {
                    this.applyBillingAddress();
                }
            },

            /**
             * Apply resolved billing address to cart
             */
            applyBillingAddress: function () {
                var shippingAddress;

                if (billingAddressModel.address()) {
                    selectBillingAddress(billingAddressModel.address());

                    return;
                }
                shippingAddress = shippingAddressModel.address();

                if (shippingAddress &&
                    shippingAddress.canUseForBilling() &&
                    (shippingAddress.isDefaultShipping() || !cart.cartData().is_virtual)
                ) {
                    selectBillingAddress(shippingAddressModel.address());
                }
            }
        };
    }
);
