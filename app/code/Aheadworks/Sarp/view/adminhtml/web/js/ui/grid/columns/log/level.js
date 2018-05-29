/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Aheadworks_Sarp/ui/grid/cells/log/level'
        },

        /**
         * Get level class name
         *
         * @param {Object} row
         * @returns {string}
         */
        getLevelClass: function (row) {
            return row[this.index + '_levelClass'];
        }
    });
});
