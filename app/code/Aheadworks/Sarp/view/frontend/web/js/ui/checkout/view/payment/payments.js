/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'paypal',
                component: 'Aheadworks_Sarp/js/ui/checkout/view/payment/method-renderer/paypal-express'
            }
        );
        rendererList.push(
            {
                type: 'authorizenet',
                component: 'Aheadworks_Sarp/js/ui/checkout/view/payment/method-renderer/authorizenet'
            }
        );
        rendererList.push(
            {
                type: 'stripe',
                component: 'Aheadworks_Sarp/js/ui/checkout/view/payment/method-renderer/stripe'
            }
        );
        rendererList.push(
            {
                type: 'adyen_cc',
                component: 'Aheadworks_Sarp/js/ui/checkout/view/payment/method-renderer/adyen/cc'
            }
        );

        return Component.extend({});
    }
);
