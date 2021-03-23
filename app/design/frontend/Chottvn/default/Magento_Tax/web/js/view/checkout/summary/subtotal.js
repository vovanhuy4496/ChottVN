/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
], function(Component, quote, totals) {
    'use strict';

    var displaySubtotalMode = window.checkoutConfig.reviewTotalsDisplayMode;

    return Component.extend({
        defaults: {
            displaySubtotalMode: displaySubtotalMode,
            template: 'Magento_Tax/checkout/summary/subtotal'
        },
        totals: quote.getTotals(),

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

        getTitle: function() {
            return window.getOriginalTotalTitle;
        },

        /**
         * @return {Boolean}
         */
        isBothPricesDisplayed: function() {
            return this.displaySubtotalMode == 'both'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {Boolean}
         */
        isIncludingTaxDisplayed: function() {
            return this.displaySubtotalMode == 'including'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*|String}
         */
        getValueInclTax: function() {
            var price = 0;

            if (this.totals()) {
                price = this.totals()['subtotal_incl_tax'];
            }

            return this.getFormattedPrice(price);
        }
    });
});