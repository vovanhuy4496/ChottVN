/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'jquery',
    'Magento_Tax/js/view/checkout/summary/shipping',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals'
], function($, Component, quote, totals) {
    'use strict';

    return Component.extend({
        totals: quote.getTotals(),

        /**
         * @override
         */
        isCalculated: function() {
            return !!quote.shippingMethod();
        },

        /**
         * @override
         */
        getShippingMethodTitle: function() {
            return '(' + this._super() + ')';
        },

        /**
         * @return over, accept
         */
        getBooleanChottvnHandlingOverWeight: function() {
            var overWeightValue = window.overWeightValue;

            if (quote.shippingMethod() != null) {
                var flag = quote.shippingMethod()['flag_shipping'];
                if (flag == 'freeshipping') {
                    return 'freeshipping';
                }
            }

            var total_weight = 0,
                products = quote.getItems(),
                inputQty_ = 0;

            for (var i = 0; i < products.length; i++) {
                if (products[i].weight != null && products[i].ampromo_rule_id == null) {
                    if (Number($("input[id='cart-" + products[i].item_id + "-qty']").val()) > 0) {
                        inputQty_ = Number($("input[id='cart-" + products[i].item_id + "-qty']").val());
                        total_weight = Number(total_weight) + (Number(products[i].weight) * inputQty_);
                    }
                }
            }
            if (total_weight >= Number(overWeightValue)) {
                return "over";
            } else {
                return "accept";
            }
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
         * @return {*}
         */
        // getValue: function() {
        //     // neu nhu chua chon address
        //     if (quote.shippingAddress() == null) {
        //         return 'Đang tính';
        //     }

        //     if (quote.shippingAddress() != null && typeof quote.shippingAddress().regionId == 'undefined' && quote.shippingAddress().regionId == '') {
        //         return 'Chưa bao gồm';
        //     }
        //     var price = totals.getSegment('shipping').value;
        //     if (price > 0) {
        //         return this.getFormattedPrice(price);
        //     }

        //     var method_code = this.getChottvnShippingMethodCode();
        //     if (method_code == "flatrate") {
        //         return 'Liên hệ sau';
        //     }
        //     if (method_code == "storepickupshipping") {
        //         return 'Miễn phí';
        //     }

        //     var checkRule = this.getBooleanChottvnHandlingOverWeight();
        //     if (checkRule == "over") {
        //         return 'Liên hệ';
        //     }
        //     if (checkRule == "freeshipping") {
        //         return 'Miễn phí';
        //     }
        //     if (checkRule == "accept" && price > 0) {
        //         return this.getFormattedPrice(price);
        //     }

        //     return 'Chưa bao gồm';
        // },
        getValue: function() {
            // if (quote.shippingAddress() != null && typeof quote.shippingAddress().regionId == 'undefined' && quote.shippingAddress().regionId == '') {
            //     return 'Chưa bao gồm';
            // }
            var price, flagShipping = totals.getSegment('shipping').area;

            if (flagShipping) {
                return flagShipping;
            }

            if (!this.isCalculated()) {
                return this.notCalculatedMessage;
            }
            price = totals.getSegment('shipping').value;

            return this.getFormattedPrice(price);
        },
    });
});