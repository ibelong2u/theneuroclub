/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'Aheadworks_Sarp/js/ui/checkout/view/payment/cc-form'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_Sarp/checkout/payment/method-renderer/authorizenet',
                timeoutMessage: 'Sorry, but something went wrong. Please contact the seller.',
                formSelector: '[data-role=authorizenet-cc-form]'
            },

            /**
             * @inheritdoc
             */
            isShowLegend: function () {
                return true;
            },

            /**
             * @inheritdoc
             */
            getCode: function () {
                return 'authorizenet';
            },

            /**
             * @inheritdoc
             */
            isActive: function () {
                return true;
            },

            /**
             * @inheritdoc
             */
            validate: function () {
                var form = $(this.formSelector);

                form.validation();

                return form.validation('isValid');
            }
        });
    }
);
