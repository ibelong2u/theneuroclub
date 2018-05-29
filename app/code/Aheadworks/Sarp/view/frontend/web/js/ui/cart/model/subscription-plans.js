/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    ['jquery'],
    function($) {
        'use strict';

        return {
            /**
             * Get subscription plans
             *
             * @returns {Array}
             */
            getItems: function () {
                return window.awSarpCheckoutConfig.subscriptionPlans;
            },

            /**
             * Get item by Id
             *
             * @param {number} planId
             * @returns {Object|undefined}
             */
            getItemById: function (planId) {
                var item;

                $.each(this.getItems(), function () {
                    if (this.subscription_plan_id == planId) {
                        item = this;
                    }
                });

                return item;
            }
        };
    }
);
