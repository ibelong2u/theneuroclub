/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'uiComponent'
    ],
    function (Component) {
        'use strict';

        var imageData = window.awSarpCheckoutConfig.imageData;

        return Component.extend({
            defaults: {
                template: 'Aheadworks_Sarp/checkout/summary/item/details/thumbnail'
            },
            displayArea: 'before_details',
            imageData: imageData,

            /**
             * Get image item
             *
             * @param {Object} item
             * @returns {Array}
             */
            getImageItem: function(item) {
                if (this.imageData[item.item_id]) {
                    return this.imageData[item.item_id];
                }
                return [];
            },

            /**
             * Get src
             *
             * @param {Object} item
             * @returns {String|null}
             */
            getSrc: function(item) {
                if (this.imageData[item.item_id]) {
                    return this.imageData[item.item_id]['src'];
                }
                return null;
            },

            /**
             * Get wight
             *
             * @param {Object} item
             * @returns {number|null}
             */
            getWidth: function(item) {
                if (this.imageData[item.item_id]) {
                    return this.imageData[item.item_id]['width'];
                }
                return null;
            },

            /**
             * Get height
             *
             * @param {Object} item
             * @returns {number|null}
             */
            getHeight: function(item) {
                if (this.imageData[item.item_id]) {
                    return this.imageData[item.item_id]['height'];
                }
                return null;
            },

            /**
             * Get alt
             *
             * @param {Object} item
             * @returns {String|null}
             */
            getAlt: function(item) {
                if (this.imageData[item.item_id]) {
                    return this.imageData[item.item_id]['alt'];
                }
                return null;
            }
        });
    }
);
