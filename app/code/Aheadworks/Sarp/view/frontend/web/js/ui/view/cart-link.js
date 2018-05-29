/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            this.subscriptionCart = customerData.get('aw-sarp-subscription-cart');
        }
    });
});
