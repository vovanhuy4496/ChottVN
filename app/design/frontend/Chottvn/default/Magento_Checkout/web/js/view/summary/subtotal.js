/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals'
], function(Component, quote, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/subtotal'
        },
        totals: quote.getTotals(),
        // /**
        //  * Get pure value.
        //  *
        //  * @return {*}
        //  */
        // getPureValue: function() {
        //     var totals = quote.getTotals()();

        //     if (totals) {
        //         return totals.subtotal;
        //     }

        //     return quote.subtotal;
        // },

        // /**
        //  * @return {*|String}
        //  */
        // getValue: function() {
        //     return this.getFormattedPrice(this.getPureValue());
        // }

        /**
         * @return {*|String}
         */
        getValue: function() {
            var price = 0,
                originalTotal = totals.getSegment('original_total').value;

            if (originalTotal && originalTotal > 0) {
                price = originalTotal;

                return this.getFormattedPrice(price);
            }

            if (this.totals()) {
                price = this.totals().subtotal;
            }

            return this.getFormattedPrice(price);
        },

    });
});