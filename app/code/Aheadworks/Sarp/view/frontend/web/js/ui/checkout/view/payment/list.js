/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/model/payment/renderer-list',
    'uiLayout',
    'Aheadworks_Sarp/js/ui/checkout/model/checkout-data-resolver'
], function (
    _,
    ko,
    utils,
    Component,
    paymentMethods,
    rendererList,
    layout,
    checkoutDataResolver
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Aheadworks_Sarp/checkout/payment-methods/list',
            visible: paymentMethods().length > 0
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super().initChildren();
            paymentMethods.subscribe(
                function (changes) {
                    checkoutDataResolver.resolvePaymentMethod();
                    _.each(changes, function (change) {
                        if (change.status === 'deleted') {
                            this.removeRenderer(change.value.method);
                        }
                    }, this);
                    _.each(changes, function (change) {
                        if (change.status === 'added') {
                            this.createRenderer(change.value);
                        }
                    }, this);
                }, this, 'arrayChange');

            return this;
        },

        /**
         * Create renders for child payment methods
         *
         * @returns {Component}
         */
        initChildren: function () {
            var self = this;

            _.each(paymentMethods(), function (paymentMethodData) {
                self.createRenderer(paymentMethodData);
            });

            return this;
        },

        /**
         * Creates component
         *
         * @returns {Component}
         */
        createComponent: function (payment) {
            var rendererTemplate,
                rendererComponent,
                templateData;

            templateData = {
                parentName: this.name,
                name: payment.name
            };
            rendererTemplate = {
                parent: '${ $.$data.parentName }',
                name: '${ $.$data.name }',
                displayArea: 'payment-method-items',
                component: payment.component
            };
            rendererComponent = utils.template(rendererTemplate, templateData);
            utils.extend(rendererComponent, {
                item: payment.item,
                config: payment.config
            });

            return rendererComponent;
        },

        /**
         * Create renderer
         *
         * @param {Object} paymentMethodData
         */
        createRenderer: function (paymentMethodData) {
            var isRendererForMethod = false;

            _.find(rendererList(), function (renderer) {

                if (renderer.hasOwnProperty('typeComparatorCallback') &&
                    typeof renderer.typeComparatorCallback == 'function'
                ) {
                    isRendererForMethod = renderer.typeComparatorCallback(renderer.type, paymentMethodData.method);
                } else {
                    isRendererForMethod = renderer.type === paymentMethodData.method;
                }

                if (isRendererForMethod) {
                    layout(
                        [
                            this.createComponent(
                                {
                                    config: renderer.config,
                                    component: renderer.component,
                                    name: renderer.type,
                                    method: paymentMethodData.method,
                                    item: paymentMethodData
                                }
                            )
                        ]
                    );
                }
            }.bind(this));
        },

        /**
         * Remove view renderer
         *
         * @param {String} paymentMethodCode
         */
        removeRenderer: function (paymentMethodCode) {
            var items = this.getRegion('payment-method-items');

            _.find(items(), function (value) {
                if (value.item.method.indexOf(paymentMethodCode) === 0) {
                    value.disposeSubscriptions();
                    value.destroy();
                }
            }, this);
        }
    });
});
