/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    $.widget('mage.awSarpSavingEstimation', {
        configuredPrice: 0,
        bundleSelectionsPricesCache: {},
        options: {
            priceBox: '.price-box',
            addToCartButton: '#product-addtocart-button',
            regularPricesConfig: {},
            subscribeButton: '[data-role=aw-sarp-product-subscribe-button]'
        },

        /**
         * Initialize widget
         */
        _create: function () {
            this.configuredPrice = this.options.regularPricesConfig.priceWithoutOptions;
            this._bind();
        },

        /**
         * @inheritdoc
         */
        _init: function () {
            this.estimate();
        },

        /**
         * Event binding
         */
        _bind: function () {
            $(this.options.priceBox).on('updatePrice', this._onUpdatePrice.bind(this));
        },

        /**
         * On update price event handler
         *
         * @param {Event} event
         * @param {Object} priceChange
         * @private
         */
        _onUpdatePrice: function (event, priceChange) {
            this._updateConfiguredPrice(priceChange);
            this.estimate();
        },

        /**
         * Update configured price
         *
         * @param {Object} priceChange
         * @private
         */
        _updateConfiguredPrice: function (priceChange) {
            var productType = this.options.regularPricesConfig.productType,
                configuredPrice = this.options.regularPricesConfig.priceWithoutOptions;

            if (productType == 'bundle') {
                $.extend(this.bundleSelectionsPricesCache, priceChange);
                _.each(this.bundleSelectionsPricesCache, function (value) {
                    if ('finalPrice' in value) {
                        configuredPrice += value.finalPrice.amount;
                    }
                });
            } else if (productType == 'configurable') {
                _.each(priceChange, function (value) {
                    if ('finalPrice' in value) {
                        configuredPrice += value.finalPrice.amount;
                    }
                });
            }

            this.configuredPrice = configuredPrice;
        },

        /**
         * Estimate saving value
         */
        estimate: function () {
            $(this.options.subscribeButton).trigger('updateTitle', this.getEstimate());
        },

        /**
         * Get estimated saving value
         *
         * @returns {number}
         */
        getEstimate: function () {
            var productType = this.options.regularPricesConfig.productType;

            return productType == 'bundle'
                ? this._getSavingAmountForBundle()
                : (productType == 'configurable'
                    ? this._getSavingAmountForConfigurable()
                    : this._getSavingAmountForDefault()
            );
        },

        /**
         * Get saving amount for default product types
         *
         * @returns {number}
         * @private
         */
        _getSavingAmountForDefault: function () {
            $(this.options.subscribeButton).trigger('updateVisibility', true);
            this._updateAddToCartButtonVisibility(this.options.regularPricesConfig.isAddToCartAvailable);
            return this._calculateSavingValue(
                this.options.regularPricesConfig.regularPrice,
                this.configuredPrice
            );
        },

        /**
         * Get saving amount for bundle product
         *
         * @returns {number}
         * @private
         */
        _getSavingAmountForBundle: function () {
            var options = this.options.regularPricesConfig.options,
                formData = this.element.serializeArray(),
                regularPricesQty = {},
                regularPriceBundle = 0,
                selectedOptionIds = [],
                isAllSubscriptionOptionsSelected = true,
                isNonSubscriptionOptionSelected = false,
                isSubscribeBtnAvailable,
                isAddToCartBtnAvailable = true;

            // Determine selected option Ids
            _.each(options, function (optionValues, fieldName) {
                var optionField = _.find(formData, function (field) {
                    return field.name == fieldName;
                });

                if (optionField && optionField.value in optionValues) {
                    var optionId = optionValues[optionField.value].optionId;

                    if (_.indexOf(selectedOptionIds, optionId) == -1) {
                        selectedOptionIds.push(optionId)
                    }
                }
            });
            // Check that all subscription options are selected
            _.each(options, function (optionValues, fieldName) {
                var optionField = _.find(formData, function (field) {
                    return field.name == fieldName;
                });

                if (!optionField || !(optionField.value in optionValues)) {
                    var valueWithSubscribeAvailable = _.find(optionValues, function (value) {
                            return value.isSubscribeAvailable;
                        });

                    if (valueWithSubscribeAvailable
                        && _.indexOf(selectedOptionIds, valueWithSubscribeAvailable.optionId) == -1
                    ) {
                        isAllSubscriptionOptionsSelected = false;
                    }
                } else {
                    var optionValue = optionValues[optionField.value];

                    if (!optionValue.isSubscribeAvailable) {
                        isAllSubscriptionOptionsSelected = false;
                    } else {
                        regularPricesQty[optionValue.value] = {
                            inputName: optionValue.inputQtyName,
                            defaultQty: optionValue.defaultQty
                                ? optionValue.defaultQty
                                : 1
                        };
                        if (!optionValue.isAddToCartAvailable) {
                            isAddToCartBtnAvailable = false;
                        }
                    }
                }
            });
            // Check that no non subscription options are selected
            _.each(formData, function (field) {
                if (field.name in options) {
                    if (!(field.value in options[field.name])) {
                        isNonSubscriptionOptionSelected = true;
                    } else {
                        var optionValue = options[field.name][field.value];

                        if (!optionValue.isSubscribeAvailable) {
                            isNonSubscriptionOptionSelected = true;
                        }
                    }
                }
            });

            isSubscribeBtnAvailable = selectedOptionIds.length
                && isAllSubscriptionOptionsSelected
                && !isNonSubscriptionOptionSelected;

            if (isSubscribeBtnAvailable) {
                _.each(regularPricesQty, function (qtyOptions, regularPrice) {
                    var qtyField = _.find(formData, function (field) {
                            return field.name == qtyOptions.inputName;
                        }),
                        qty = qtyField ? qtyField.value : qtyOptions.defaultQty;

                    regularPriceBundle += qty * regularPrice;
                });
            } else {
                isAddToCartBtnAvailable = true;
            }

            $(this.options.subscribeButton).trigger('updateVisibility', isSubscribeBtnAvailable);
            this._updateAddToCartButtonVisibility(isAddToCartBtnAvailable);

            return this._calculateSavingValue(regularPriceBundle, this.configuredPrice);
        },

        /**
         * Get saving amount for configurable product
         *
         * @returns {number}
         * @private
         */
        _getSavingAmountForConfigurable: function () {
            var options = this.options.regularPricesConfig.options,
                subscriptionProducts = options.products,
                regularPrices = options.regularPrices,
                addToCartAvailability = options.addToCartAvailability,
                formData = this.element.serializeArray(),
                regularPrice = 0,
                childProductId = 0,
                isChildSelected = true,
                isAddToCartAvailable = true;

            _.each(formData, function (field) {
                if (field.name in subscriptionProducts) {
                    if (field.value in subscriptionProducts[field.name]) {
                        childProductId = subscriptionProducts[field.name][field.value];
                        regularPrice = regularPrices[childProductId];
                        isAddToCartAvailable = addToCartAvailability[childProductId];
                    } else {
                        isChildSelected = false;
                    }
                }
            });
            isChildSelected = isChildSelected && !!childProductId;

            $(this.options.subscribeButton).trigger('updateVisibility', isChildSelected);
            this._updateAddToCartButtonVisibility(isChildSelected && isAddToCartAvailable || !isChildSelected);

            return this._calculateSavingValue(regularPrice, this.configuredPrice);
        },

        /**
         * Calculate saving value in percents
         *
         * @param {number} regularPrice
         * @param {number} price
         * @returns {number}
         * @private
         */
        _calculateSavingValue: function (regularPrice, price) {
            var savingValue = 0;

            if (regularPrice < price) {
                savingValue = Math.floor(100 - regularPrice * 100 / price);
                return savingValue > 1 ? savingValue : 0;
            }
            return savingValue;
        },

        /**
         * Update visibility of Add To Cart button
         *
         * @param {boolean} isVisible
         * @private
         */
        _updateAddToCartButtonVisibility: function (isVisible) {
            var addToCartButton = $(this.options.addToCartButton);

            if (isVisible) {
                addToCartButton.show();
                this.element
                    .find(this.options.subscribeButton)
                    .removeClass('primary');
            } else {
                addToCartButton.hide();
                this.element
                    .find(this.options.subscribeButton)
                    .addClass('primary');
            }
        }
    });

    return $.mage.awSarpSavingEstimation;
});
