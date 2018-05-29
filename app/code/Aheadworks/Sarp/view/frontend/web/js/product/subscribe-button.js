/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'mage/translate',
    'mage/template'
], function ($, $t, mageTemplate) {
    'use strict';

    $.widget('mage.awSarpSubscribeButton', {
        options: {
            container: '[data-role=aw-sarp-product-subscribe-button-container]',
            form: '#product_addtocart_form',
            addToCartButton: '#product-addtocart-button',
            subscribeUrl: '#',
            buttonText: '[data-role=button-text]',
            messagesSelector: '[data-placeholder=messages]',
            defaultTitle: 'Subscribe',
            titleWithSavingEstimationTemplate: '[data-role=title-with-saving-estimation]',
            isSavingEstimationEnabled: true
        },

        originalButtonText: '',
        titleWithSavingEstimationTemplate: '',

        /**
         * Initialize widget
         */
        _create: function () {
            this.originalButtonText = this.element.find(this.options.buttonText).text();
            this.titleWithSavingEstimationTemplate = mageTemplate(
                this.options.titleWithSavingEstimationTemplate
            );
            this._bind();
        },

        /**
         * @inheritdoc
         */
        _init: function () {
            var form = $(this.options.form),
                estimate = form.data('mageAwSarpSavingEstimation')
                    ? form.awSarpSavingEstimation('getEstimate')
                    : 0;

            if (estimate) {
                this._updateTitle(estimate);
            }
        },

        /**
         * Event binding
         */
        _bind: function () {
            this._on({
                'click': '_submitForm',
                'updateVisibility': '_onUpdateVisibility',
                'updateTitle': '_onUpdateTitle'
            });
        },

        /**
         * Submit product form
         */
        _submitForm: function () {
            var self = this,
                form = $(this.options.form),
                addToCartButton = $(this.options.addToCartButton);

            $.ajax({
                url: this.options.subscribeUrl,
                data: form.serialize(),
                type: 'post',
                dataType: 'json',

                /**
                 * A pre-request callback
                 */
                beforeSend: function () {
                    addToCartButton.prop('disabled', true);
                    self.element.prop('disabled', true);
                    self.setButtonText('Processing...')
                },

                /**
                 * Called when request succeeds
                 *
                 * @param {Object} response
                 */
                success: function(response) {
                    if (response.redirectUrl) {
                        window.location = response.redirectUrl;
                    }
                    if (response.messages) {
                        $(self.options.messagesSelector).html(response.messages);
                    }
                },

                /**
                 * Called when request finishes
                 */
                complete: function () {
                    addToCartButton.removeProp('disabled');
                    self.element.removeProp('disabled');
                    self.restoreButtonText();
                }
            });
        },

        /**
         * On update visibility event handler
         *
         * @param {Event} event
         * @param {Boolean} isVisible
         * @private
         */
        _onUpdateVisibility: function (event, isVisible) {
            var container = $(this.options.container);

            if (isVisible) {
                container.show();
            } else {
                container.hide();
            }
        },

        /**
         * On update title event handler
         *
         * @param {Event} event
         * @param {number} amount
         * @private
         */
        _onUpdateTitle: function (event, amount) {
            this._updateTitle(amount);
        },

        /**
         * Update title
         *
         * @param {number} amount
         * @private
         */
        _updateTitle: function (amount) {
            if (this.options.isSavingEstimationEnabled) {
                if (amount > 0) {
                    this.originalButtonText = this.titleWithSavingEstimationTemplate({amount: amount});
                } else {
                    this.originalButtonText = this.options.defaultTitle;
                }
                this.restoreButtonText();
            }
        },

        /**
         * Set button text
         *
         * @param {string} text
         */
        setButtonText: function (text) {
            this.element
                .find(this.options.buttonText)
                .text($t(text));
        },

        /**
         * Restore button text to original one
         */
        restoreButtonText: function () {
            this.element
                .find(this.options.buttonText)
                .text(this.originalButtonText);
        }
    });

    return $.mage.awSarpSubscribeButton;
});
