define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils'
    ],
    function($, Component, quote, totals, priceUtils) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Magento_Tax/checkout/summary/savings_amount'
            },
            totals: quote.getTotals(),

            /**
             * @return {*}
             */
            isDisplayed: function() {
                return this.isFullMode();
            },

            validateSavingsAmount: function() {
                return parseInt(totals.getSegment('savings_amount').value);
            },

            getSavingsAmountTotal: function() {
                var price = totals.getSegment('savings_amount').value;
                return this.getFormattedPrice(price);
                // var savings_amount = 0,
                //     originalTotal = quote.originalTotal,
                //     shipping_price = typeof quote.shippingAmount != 'undefined' ? quote.shippingAmount : totals.getSegment('shipping').value,
                //     final_price = totals.getSegment('grand_total').value;

                // savings_amount = originalTotal - final_price - shipping_price;

                // quote.savingsAmount = savings_amount > 0 ? savings_amount : 0;

                // return this.getFormattedPrice(savings_amount);
            },

            // getBaseSavingsAmountTotal: function() {
            //     var savings_amount = 0,
            //         originalTotal = quote.originalTotal,
            //         shipping_price = typeof quote.shippingAmount != 'undefined' ? quote.shippingAmount : totals.getSegment('shipping').value,
            //         final_price = totals.getSegment('grand_total').value;

            //     savings_amount = originalTotal - final_price - shipping_price;

            //     quote.savingsAmount = savings_amount > 0 ? savings_amount : 0;

            //     return this.getFormattedPrice(savings_amount);
            // }
        });
    }
);