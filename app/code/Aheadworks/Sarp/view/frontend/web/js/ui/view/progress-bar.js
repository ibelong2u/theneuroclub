/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery',
        'underscore',
        'ko',
        'uiComponent',
        'Aheadworks_Sarp/js/ui/model/step-navigator',
        'jquery/jquery.hashchange'
    ],
    function ($, _, ko, Component, stepNavigator) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_Sarp/cart/progress-bar',
                steps: []
            },

            /**
             * @inheritdoc
             */
            initialize: function() {
                this._super()
                    .initSteps();

                $(window).hashchange(_.bind(stepNavigator.handleHash, stepNavigator));
                stepNavigator.handleHash();
            },

            /**
             * Init steps
             *
             * @returns {exports}
             */
            initSteps: function () {
                $.each(this.steps, function () {
                    stepNavigator.registerStep(this);
                });
                return this;
            },

            /**
             * Get steps observable
             *
             * @returns {Function}
             */
            getSteps: function () {
                return stepNavigator.steps;
            },

            /**
             * Sort items
             *
             * @param {Object} itemOne
             * @param {Object} itemTwo
             * @returns {*|number}
             */
            sortItems: function(itemOne, itemTwo) {
                return stepNavigator.sortItems(itemOne, itemTwo);
            },

            /**
             * Navigate to step
             *
             * @param {Object} step
             */
            navigateTo: function(step) {
                stepNavigator.navigateBackTo(step.code);
            },

            /**
             * Check if item is processed
             *
             * @param {Object} item
             * @returns {*|boolean}
             */
            isProcessed: function(item) {
                return stepNavigator.isProcessed(item.code);
            }
        });
    }
);
