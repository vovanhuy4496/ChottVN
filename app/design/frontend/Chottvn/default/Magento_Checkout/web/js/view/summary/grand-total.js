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
            template: 'Magento_Checkout/summary/grand-total'
        },

        /**
         * @return {*}
         */
        isDisplayed: function() {
            return this.isFullMode();
        },

        /**
         * Get pure value.
         */
        getPureValue: function() {
            var totals = quote.getTotals()();

            if (totals) {
                return totals['grand_total'];
            }

            return quote['grand_total'];
        },

        getTitle: function() {
            return totals.getSegment('grand_total').title;
        },

        /**
         * @return {*|String}
         */
        getValue: function() {
            return this.getFormattedPrice(this.getPureValue());
        }
    });
});