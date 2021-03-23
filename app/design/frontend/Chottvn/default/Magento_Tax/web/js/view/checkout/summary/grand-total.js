/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'
], function($, Component, quote, priceUtils, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
            template: 'Magento_Tax/checkout/summary/grand-total'
        },
        totals: quote.getTotals(),
        isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,

        /**
         * @return {*}
         */
        isDisplayed: function() {
            return this.isFullMode();
        },

        /**
         * @return {*|String}
         */
        // getValue: function() {
        //     var final_price = 0,
        //         shipping_price = this.shippingPrice();

        //     if (this.totals()) {
        //         final_price = totals.getSegment('grand_total').value;
        //         this.getCustomTitle(shipping_price);

        //         return this.getFormattedPrice(final_price);
        //     }

        //     return 'Đang tính';
        // },

        /**
         * @return {*|String}
         */
        getValue: function() {
            var price = 0;

            if (this.totals()) {
                price = totals.getSegment('grand_total').value;
            }

            return this.getFormattedPrice(price);
        },

        getTitle: function() {
            return totals.getSegment('grand_total').title;
        },

        shippingPrice: function() {
            // neu nhu chua chon address
            if (quote.shippingAddress() == null) {
                return 0;
            }
            if (quote.shippingAddress() != null && typeof quote.shippingAddress().regionId == 'undefined' && quote.shippingAddress().regionId == '') {
                return 0;
            }
            var price = totals.getSegment('shipping').value;

            if (price > 0) {
                return price;
            }

            var method_code = this.getChottvnShippingMethodCode();
            if (method_code == "flatrate") {
                return 0;
            }
            if (method_code == "storepickupshipping") {
                return 0;
            }

            var flagShipping = this.flagShipping();
            // console.log(flagShipping);

            if (flagShipping == "over") {
                return 'Liên hệ';
            }
            if (flagShipping == "freeshipping") {
                return 'Miễn phí';
            }
            if (flagShipping == "accept" && price > 0) {
                return this.getFormattedPrice(price);
            }

            var checkRule = this.getBooleanChottvnHandlingOverWeight();
            if (checkRule == "over") {
                return 0;
            }
            if (checkRule == "freeshipping") {
                return "freeshipping";
            }
            if (checkRule == "accept" && price > 0) {
                return price;
            }
            return 0;
        },

        getChottvnShippingMethodCode: function() {
            if (!this.isCalculated()) {
                return '';
            }
            var shippingMethod = quote.shippingMethod(),
                method_code = shippingMethod['method_code'] ? shippingMethod['method_code'] : shippingMethod['carrier_code'];

            return method_code;
        },

        /**
         * @return over, accept
         */
        getBooleanChottvnHandlingOverWeight: function() {
            if (quote.shippingMethod() != null) {
                var flag = quote.shippingMethod()['flag_shipping'];
                if (flag == 'freeshipping') {
                    return 'freeshipping';
                }
            }
            if (this.isOverWeight()) {
                return "over";
            } else {
                return "accept";
            }
        },

        /**
         * @return {*|Boolean}
         */
        isCalculated: function() {
            return this.totals() && this.isFullMode() && quote.shippingMethod() != null; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*|String}
         */
        getCustomTitle: function(shipping_price) {
            var title = window.title_grand_total;
            if (shipping_price > 0) {
                // trick update html
                $(".grand.totals .mark strong").html(title + ':');
                $(".grand.totals .amount").data('data-th', title);

                return title;
            }

            if ((this.isOverWeight() && shipping_price != 'freeshipping') || shipping_price == 0) {
                title = window.title_grand_total_temp;
            }

            // trick update html
            $(".grand.totals .mark strong").html(title + ':');
            $(".grand.totals .amount").data('data-th', title);

            return title;
        },

        /**
         * @return freeshipping, over, accept
         */
        flagShipping: function() {
            // console.log(quote);
            if (quote.shippingMethod() != null) {
                var flag = quote.shippingMethod()['flag_shipping'];
                return flag;
            }
            return false;
        },

        /**
         * @return {*|String}
         */
        isOverWeight: function() {
            var total_weight = 0,
                overWeightValue = window.overWeightValue,
                products = quote.getItems(),
                inputQty_ = 0;

            for (var i = 0; i < products.length; i++) {
                if (products[i].weight != null && products[i].ampromo_rule_id == null) {
                    if (Number($("input[id='inputQty_" + products[i].item_id + "']").val()) > 0) {
                        inputQty_ = Number($("input[id='inputQty_" + products[i].item_id + "']").val());
                    }
                    if (Number($("input[id='cart-" + products[i].item_id + "-qty']").val()) > 0) {
                        inputQty_ = Number($("input[id='cart-" + products[i].item_id + "-qty']").val());
                    }
                    total_weight = Number(total_weight) + Number(products[i].weight) * inputQty_;
                }
            }
            // if free shipping , will don't use over weight 
            if (total_weight > Number(overWeightValue)) {
                return true;
            }

            return false;
        },

        /**
         * @return {*|String}
         */
        getBaseValue: function() {
            var price = 0;

            if (this.totals()) {
                price = this.totals()['base_grand_total'];
            }

            return priceUtils.formatPrice(price, quote.getBasePriceFormat());
        },

        /**
         * @return {*}
         */
        getGrandTotalExclTax: function() {
            var total = this.totals();

            if (!total) {
                return 0;
            }

            return this.getFormattedPrice(total['grand_total']);
        },

        /**
         * @return {Boolean}
         */
        isBaseGrandTotalDisplayNeeded: function() {
            var total = this.totals();

            if (!total) {
                return false;
            }

            return total['base_currency_code'] != total['quote_currency_code']; //eslint-disable-line eqeqeq
        }
    });
});