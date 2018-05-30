/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'uiComponent',
        'Aheadworks_Sarp/js/ui/cart/model/cart',
        'Magento_Catalog/js/price-utils'
    ],
    function (Component, cart, priceUtils) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Tax/checkout/shipping_method/price'
            },

            isDisplayShippingPriceExclTax: window.checkoutConfig.isDisplayShippingPriceExclTax,
            isDisplayShippingBothPrices: window.checkoutConfig.isDisplayShippingBothPrices,

            isPriceEqual: function(item) {
                return item.price_excl_tax != item.price_incl_tax;
            },

            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price, cart.getPriceFormat());
            }
        });
    }
);
