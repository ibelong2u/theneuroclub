/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Aheadworks_Sarp/js/ui/checkout/model/address-converter',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-address',
        'Aheadworks_Sarp/js/ui/checkout/action/create-shipping-address',
        'Aheadworks_Sarp/js/ui/checkout/action/select-shipping-address',
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-rates-validator',
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-address/form-popup-state',
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-service',
        'Aheadworks_Sarp/js/ui/checkout/action/select-shipping-method',
        'Aheadworks_Sarp/js/ui/checkout/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Aheadworks_Sarp/js/ui/checkout/model/checkout-data-resolver',
        'Aheadworks_Sarp/js/ui/checkout/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Aheadworks_Sarp/js/ui/checkout/model/shipping-rate-service'
    ],
    function (
        $,
        _,
        Component,
        ko,
        customer,
        addressList,
        addressConverter,
        cart,
        shippingAddressModel,
        createShippingAddressAction,
        selectShippingAddressAction,
        shippingRatesValidator,
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        setShippingInformationAction,
        stepNavigator,
        modal,
        checkoutDataResolver,
        checkoutData,
        registry,
        $t
    ) {
        'use strict';

        var popUp = null;

        return Component.extend({
            defaults: {
                template: 'Aheadworks_Sarp/checkout/shipping',
                shippingAddressFieldset: 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset',
                loginFormSelector: 'form[data-role=email-with-possible-login]'
            },
            visible: ko.observable(!cart.cartData().is_virtual),
            errorValidationMessage: ko.observable(false),
            isCustomerLoggedIn: customer.isLoggedIn,
            isFormPopUpVisible: formPopUpState.isVisible,
            isFormInline: addressList().length == 0,
            isNewAddressAdded: ko.observable(false),
            saveInAddressBook: 1,
            cartIsVirtual: cart.cartData().is_virtual,

            /**
             * @inheritdoc
             */
            initialize: function () {
                var self = this,
                    hasNewAddress;

                this._super()
                    ._initShippingRatesValidator()
                    ._registerStepNavigatorStep();

                checkoutDataResolver.resolveShippingAddress();

                hasNewAddress = addressList.some(function (address) {
                    return address.getType() == 'new-customer-address';
                });

                this.isNewAddressAdded(hasNewAddress);

                this.isFormPopUpVisible.subscribe(function (value) {
                    if (value) {
                        self.getPopUp().openModal();
                    }
                });

                cart.shippingMethod.subscribe(function () {
                    self.errorValidationMessage(false);
                });

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();

                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend({}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                    checkoutProvider.on('shippingAddress', function (shippingAddressData) {
                        checkoutData.setShippingAddressFromData(shippingAddressData);
                    });
                });

                return this;
            },

            /**
             * Init shipping rates validator
             *
             * @returns {exports}
             * @private
             */
            _initShippingRatesValidator: function () {
                shippingRatesValidator.initFields(this.shippingAddressFieldset);

                return this;
            },

            /**
             * Register step navigator step
             *
             * @returns {exports}
             * @private
             */
            _registerStepNavigatorStep: function () {
                if (!cart.cartData().is_virtual) {
                    stepNavigator.registerStep(
                        'shipping',
                        '',
                        $t('Shipping'),
                        this.visible, _.bind(function () {}, this),
                        10
                    );
                }

                return this;
            },

            /**
             * Get popup
             *
             * @returns {*}
             */
            getPopUp: function () {
                if (!popUp) {
                    this._initPopup();
                }

                return popUp;
            },

            /**
             * Init popup
             *
             * @private
             */
            _initPopup: function () {
                var self = this,
                    buttons = this.popUpForm.options.buttons,
                    buttonSaveText = buttons.save.text
                        ? buttons.save.text
                        : $t('Save Address'),
                    buttonSaveClass = buttons.save.class
                        ? buttons.save.class
                        : 'action primary action-save-address',
                    buttonCancelText = buttons.cancel.text
                        ? buttons.cancel.text
                        : $t('Cancel'),
                    buttonCancelClass = buttons.cancel.class
                        ? buttons.cancel.class
                        : 'action secondary action-hide-popup';

                this.popUpForm.options.buttons = [
                    {
                        text: buttonSaveText,
                        class: buttonSaveClass,
                        click: self.saveNewAddress.bind(self)
                    },
                    {
                        text: buttonCancelText,
                        class: buttonCancelClass,
                        click: function () {
                            this.closeModal();
                        }
                    }
                ];
                this.popUpForm.options.closed = function () {
                    self.isFormPopUpVisible(false);
                };
                popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
            },

            /**
             * Show address form popup handler
             */
            showFormPopUp: function () {
                this.isFormPopUpVisible(true);
            },

            /**
             * Save new shipping address
             */
            saveNewAddress: function () {
                var addressData,
                    newShippingAddress;

                this.source.set('params.invalid', false);
                this._triggerShippingDataValidateEvent();

                if (!this.source.get('params.invalid')) {
                    addressData = this.source.get('shippingAddress');
                    addressData.is_save_in_address_book = this.saveInAddressBook ? 1 : 0;

                    newShippingAddress = createShippingAddressAction(addressData);
                    selectShippingAddressAction(newShippingAddress);
                    checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                    checkoutData.setNewCustomerShippingAddress(addressData);
                    this.getPopUp().closeModal();
                    this.isNewAddressAdded(true);
                }
            },

            /**
             * Shipping Method View
             */
            rates: shippingService.getShippingRates(),
            isLoading: shippingService.isLoading,
            isSelected: ko.computed(function () {
                    return cart.shippingMethod() ?
                    cart.shippingMethod().carrier_code + '_' + cart.shippingMethod().method_code
                        : null;
                }
            ),

            /**
             * Select shipping method handler
             *
             * @param {Object} shippingMethod
             * @return {Boolean}
             */
            selectShippingMethod: function (shippingMethod) {
                selectShippingMethodAction(shippingMethod);
                checkoutData.setSelectedShippingRate(
                    shippingMethod.carrier_code + '_' + shippingMethod.method_code
                );

                return true;
            },

            /**
             * Set shipping information handler
             */
            setShippingInformation: function () {
                if (this._validateShippingInformation()) {
                    setShippingInformationAction().done(
                        function () {
                            stepNavigator.next();
                        }
                    );
                }
            },

            /**
             * Validate shipping information
             *
             * @returns {boolean}
             * @private
             */
            _validateShippingInformation: function () {
                var shippingAddress,
                    addressData,
                    emailValidationResult = customer.isLoggedIn();

                if (!cart.shippingMethod()) {
                    this.errorValidationMessage($.mage.__('Please specify a shipping method.'));

                    return false;
                }

                if (!customer.isLoggedIn()) {
                    $(this.loginFormSelector).validation();
                    emailValidationResult = Boolean($(this.loginFormSelector + ' input[name=username]').valid());
                }

                if (this.isFormInline) {
                    this.source.set('params.invalid', false);
                    this._triggerShippingDataValidateEvent();
                    if (emailValidationResult &&
                        this.source.get('params.invalid') ||
                        !cart.shippingMethod().method_code ||
                        !cart.shippingMethod().carrier_code
                    ) {
                        this.focusInvalid();

                        return false;
                    }

                    shippingAddress = shippingAddressModel.address();
                    addressData = addressConverter.formAddressDataToCartAddress(
                        this.source.get('shippingAddress')
                    );

                    // Copy form data to cart shipping address object
                    for (var field in addressData) {

                        if (addressData.hasOwnProperty(field) &&
                            shippingAddress.hasOwnProperty(field) &&
                            typeof addressData[field] != 'function' &&
                            _.isEqual(shippingAddress[field], addressData[field])
                        ) {
                            shippingAddress[field] = addressData[field];
                        } else if (typeof addressData[field] != 'function' &&
                            !_.isEqual(shippingAddress[field], addressData[field])) {
                            shippingAddress = addressData;
                            break;
                        }
                    }

                    if (customer.isLoggedIn()) {
                        shippingAddress.is_save_in_address_book = 1;
                    }
                    selectShippingAddressAction(shippingAddress);
                }

                if (!emailValidationResult) {
                    $(this.loginFormSelector + ' input[name=username]').focus();

                    return false;
                }

                return true;
            },

            /**
             * Trigger Shipping data Validate Event
             *
             * @private
             */
            _triggerShippingDataValidateEvent: function () {
                this.source.trigger('shippingAddress.data.validate');
            }
        });
    }
);
