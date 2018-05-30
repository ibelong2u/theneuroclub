/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko',
        'underscore',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Aheadworks_Sarp/js/ui/checkout/model/billing-address',
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-address',
        'Aheadworks_Sarp/js/ui/checkout/model/payment-method',
        'Aheadworks_Sarp/js/ui/checkout/action/create-billing-address',
        'Aheadworks_Sarp/js/ui/checkout/action/select-billing-address',
        'Aheadworks_Sarp/js/ui/checkout/checkout-data',
        'Aheadworks_Sarp/js/ui/checkout/model/checkout-data-resolver',
        'Magento_Customer/js/customer-data',
        'Aheadworks_Sarp/js/ui/checkout/action/set-billing-address',
        'Magento_Ui/js/model/messageList',
        'mage/translate'
    ],
    function (
        ko,
        _,
        Component,
        customer,
        addressList,
        cart,
        billingAddress,
        shippingAddress,
        paymentMethod,
        createBillingAddressAction,
        selectBillingAddressAction,
        checkoutData,
        checkoutDataResolver,
        customerData,
        setBillingAddressAction,
        globalMessageList,
        $t
    ) {
        'use strict';

        var lastSelectedBillingAddress = null,
            newAddressOption = {
                /**
                 * Get new address label
                 * @returns {String}
                 */
                getAddressInline: function () {
                    return $t('New Address');
                },
                customerAddressId: null
            },
            countryData = customerData.get('directory-data'),
            addressOptions = addressList().filter(function (address) {
                return address.getType() == 'customer-address';
            });

        addressOptions.push(newAddressOption);

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/billing-address'
            },
            currentBillingAddress: billingAddress.address,
            addressOptions: addressOptions,
            customerHasAddresses: addressOptions.length > 1,
            canUseShippingAddress: ko.computed(function () {
                return !cart.cartData().is_virtual
                    && shippingAddress.address()
                    && shippingAddress.address().canUseForBilling();
            }),

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super();
                paymentMethod.method.subscribe(function () {
                    checkoutDataResolver.resolveBillingAddress();
                }, this);
            },

            /**
             * @inheritdoc
             */
            initObservable: function () {
                this._super()
                    .observe({
                        selectedAddress: null,
                        isAddressDetailsVisible: billingAddress.address() != null,
                        isAddressFormVisible: !customer.isLoggedIn() || addressOptions.length == 1,
                        isAddressSameAsShipping: false,
                        saveInAddressBook: 1
                    });

                billingAddress.address.subscribe(function (newAddress) {
                    if (cart.cartData().is_virtual) {
                        this.isAddressSameAsShipping(false);
                    } else {
                        this.isAddressSameAsShipping(
                            newAddress != null &&
                            newAddress.getCacheKey() == shippingAddress.address().getCacheKey()
                        );
                    }

                    if (newAddress != null && newAddress.saveInAddressBook !== undefined) {
                        this.saveInAddressBook(newAddress.saveInAddressBook);
                    } else {
                        this.saveInAddressBook(1);
                    }
                    this.isAddressDetailsVisible(true);
                }, this);

                return this;
            },

            /**
             * @param {Object} address
             * @return {*}
             */
            addressOptionsText: function (address) {
                return address.getAddressInline();
            },

            /**
             * Use shipping address handler
             *
             * @return {Boolean}
             */
            useShippingAddress: function () {
                if (this.isAddressSameAsShipping()) {
                    selectBillingAddressAction(shippingAddress.address());
                    this._updateAddresses();
                    this.isAddressDetailsVisible(true);
                } else {
                    lastSelectedBillingAddress = billingAddress.address();
                    billingAddress.address(null);
                    this.isAddressDetailsVisible(false);
                }
                checkoutData.setSelectedBillingAddress(null);

                return true;
            },

            /**
             * Update address action handler
             */
            updateAddress: function () {
                if (this.selectedAddress() && this.selectedAddress() != newAddressOption) {
                    selectBillingAddressAction(this.selectedAddress());
                    checkoutData.setSelectedBillingAddress(this.selectedAddress().getKey());
                } else {
                    this.source.set('params.invalid', false);
                    this.source.trigger(this.dataScopePrefix + '.data.validate');

                    if (this.source.get(this.dataScopePrefix + '.custom_attributes')) {
                        this.source.trigger(this.dataScopePrefix + '.custom_attributes.data.validate');
                    }

                    if (!this.source.get('params.invalid')) {
                        var addressData = this.source.get(this.dataScopePrefix),
                            newBillingAddress;

                        if (customer.isLoggedIn() && !this.customerHasAddresses) {
                            this.saveInAddressBook(1);
                        }
                        addressData['save_in_address_book'] = this.saveInAddressBook() ? 1 : 0;
                        newBillingAddress = createBillingAddressAction(addressData);

                        selectBillingAddressAction(newBillingAddress);
                        checkoutData.setSelectedBillingAddress(newBillingAddress.getKey());
                        checkoutData.setNewCustomerBillingAddress(addressData);
                    }
                }
                this._updateAddresses();
            },

            /**
             * Edit address action handler
             */
            editAddress: function () {
                lastSelectedBillingAddress = billingAddress.address();
                billingAddress.address(null);
                this.isAddressDetailsVisible(false);
            },

            /**
             * Cancel address edit action handler
             */
            cancelAddressEdit: function () {
                var isSameAsShipping;

                this._restoreBillingAddress();
                if (billingAddress.address()) {
                    isSameAsShipping = billingAddress.address() != null
                        && billingAddress.address().getCacheKey() == shippingAddress.address().getCacheKey()
                        && !cart.cartData().is_virtual;
                    this.isAddressSameAsShipping(isSameAsShipping);
                    this.isAddressDetailsVisible(true);
                }
            },

            /**
             * Restore billing address
             *
             * @private
             */
            _restoreBillingAddress: function () {
                if (lastSelectedBillingAddress != null) {
                    selectBillingAddressAction(lastSelectedBillingAddress);
                }
            },

            /**
             * On address change event handler
             *
             * @param {Object} address
             */
            onAddressChange: function (address) {
                this.isAddressFormVisible(address == newAddressOption);
            },

            /**
             * Get country name
             *
             * @param {int} countryId
             * @return {String}
             */
            getCountryName: function (countryId) {
                return countryData()[countryId] != undefined
                    ? countryData()[countryId].name
                    : '';
            },

            /**
             * Trigger action to update shipping and billing addresses
             *
             * @private
             */
            _updateAddresses: function () {
                if (window.awSarpCheckoutConfig.reloadOnBillingAddress) {
                    setBillingAddressAction(globalMessageList);
                }
            },

            /**
             * Get code
             *
             * @param {Object} parent
             * @returns {String}
             */
            getCode: function (parent) {
                return _.isFunction(parent.getCode) ? parent.getCode() : 'shared';
            }
        });
    }
);
