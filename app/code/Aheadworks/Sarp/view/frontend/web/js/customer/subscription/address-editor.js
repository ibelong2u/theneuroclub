/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.awSarpAddressEditor', {
        options: {
            infoContainer: '[data-role=address-info]',
            form: '[data-role=address-form]',
            editAction: '[data-role=address-edit-action]',
            submitAction: '[data-role=address-submit-action]',
            cancelAction: '[data-role=address-cancel-action]',
            isScrollToEditor: true
        },

        /**
         * Initialize widget
         */
        _create: function () {
            this._bind();
        },

        /**
         * @inheritdoc
         */
        _init: function () {
            this._switch(false, this.element);
        },

        /**
         * Event binding
         */
        _bind: function () {
            var handlers = {};

            handlers['click ' + this.options.editAction] = '_onEditClick';
            handlers['click ' + this.options.cancelAction] = '_onCancelClick';
            this._on(handlers);
        },

        /**
         * Edit action click event handler
         *
         * @param {Object} event
         * @private
         */
        _onEditClick: function (event) {
            this._switch(false, $(document));
            this._switch(true, this.element);
            if (this.options.isScrollToEditor) {
                this._scrollTo(this.element);
            }
            event.preventDefault();
        },

        /**
         * Cancel action click event handler
         *
         * @param {Object} event
         * @private
         */
        _onCancelClick: function (event) {
            this._switch(false, this.element);
            event.preventDefault();
        },

        /**
         * Switch edit/info modes
         *
         * @param {boolean} toEdit
         * @param {Element} context
         * @private
         */
        _switch: function (toEdit, context) {
            var infoContainer = context.find(this.options.infoContainer),
                form = context.find(this.options.form),
                editAction = context.find(this.options.editAction);

            if (toEdit) {
                infoContainer.hide();
                editAction.hide();
                form.show();
            } else {
                infoContainer.show();
                editAction.show();
                form.hide();
            }
        },

        /**
         * Scroll to element
         *
         * @param {Element} element
         * @private
         */
        _scrollTo: function (element) {
            var body = $.browser.safari || $.browser.chrome ? $('body') : $('html'),
                screenHeight = $.browser.opera ? window.innerHeight : $(window).height(),
                elementOffset = $(element).offset();

            if ($(element).length && elementOffset.top > screenHeight) {
                body.animate({scrollTop: elementOffset.top}, 0);
            }
        }
    });

    return $.mage.awSarpAddressEditor;
});
