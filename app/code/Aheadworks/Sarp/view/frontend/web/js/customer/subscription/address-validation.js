/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'jquery/ui',
    'validation'
], function ($) {
    'use strict';

    $.widget('mage.awSarpAddressValidation', {
        options: {
            submitBtn: '[data-role=address-submit-action]'
        },

        /**
         * Initialize widget
         */
        _create: function () {
            var submitBtn = $(this.options.submitBtn, this.element);

            this.element.validation({

                /**
                 * Submit Handler
                 * @param {Element} form
                 */
                submitHandler: function (form) {
                    submitBtn.attr('disabled', true);
                    form.submit();
                }
            });
        }
    });

    return $.mage.awSarpAddressValidation;
});
