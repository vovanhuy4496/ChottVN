/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals'
], function($, Component, quote, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/shipping'
        },
        quoteIsVirtual: quote.isVirtual(),
        totals: quote.getTotals(),

        /**
         * @return {*}
         */
        getShippingMethodTitle: function() {
            var shippingMethod,
                shippingMethodTitle = '';

            if (!this.isCalculated()) {
                return '';
            }
            shippingMethod = quote.shippingMethod();

            if (typeof shippingMethod['method_title'] !== 'undefined') {
                shippingMethodTitle = shippingMethod['method_title'];
            }

            return shippingMethod ? shippingMethodTitle : '';
        },

        /**
         * @return {*}
         */
        getChottvnShippingMethodCode: function() {
            if (!this.isCalculated()) {
                return '';
            }
            var shippingMethod = quote.shippingMethod(),
                method_code = shippingMethod['method_code'] ? shippingMethod['method_code'] : shippingMethod['carrier_code'];

            return method_code;
        },

        /**
         * @return freeshipping, over, accept
         */
        flagShipping: function() {
            if (quote.shippingMethod() != null) {
                var flag = quote.shippingMethod()['flag_shipping'];
                return flag;
            }
            return false;
        },

        setMaxDeliveryDates: function() {
            var overWeightValue = window.overWeightValue;

            if (!this.isCalculated()) {
                return false;
            }

            var total_weight = 0,
                products = quote.getItems(),
                inputQty_ = 0;

            for (var i = 0; i < products.length; i++) {
                if (products[i].weight != null && products[i].ampromo_rule_id == null) {
                    if (Number($("input[id='inputQty_" + products[i].item_id + "']").val()) > 0) {
                        inputQty_ = Number($("input[id='inputQty_" + products[i].item_id + "']").val());
                    }
                    total_weight = Number(total_weight) + (Number(products[i].weight) * inputQty_);
                }
            }
            if (total_weight < Number(overWeightValue)) {
                $("input[name='fee_shipping_contact']").val(0);
                if (quote.shippingMethod() != null && quote.shippingMethod().max_delivery_dates !== null && quote.shippingMethod().max_delivery_dates !== undefined) {
                    var value = quote.shippingMethod().max_delivery_dates;
                    if (value) {
                        var today = new Date();
                        today = this.formatDate(this.addDays(today, value));
                        $('span[name="max_delivery_dates"]').text(today);
                        $('div[name="additional.max_delivery_dates"]').show();
                    } else {
                        $('div[name="additional.max_delivery_dates"]').hide();
                    }
                }
            } else {
                $("input[name='fee_shipping_contact']").val(1);
                var text_contact = window.max_delivery_dates_contact;
                $('span[name="max_delivery_dates"]').text(text_contact);
                $('div[name="additional.max_delivery_dates"]').show();
            }
        },

        /**
         * @return {*|Boolean}
         */
        addDays: function(date, days) {
            var result = new Date(date);
            result.setDate(date.getDate() + days);
            return result;
        },

        /**
         * @return {*|Boolean}
         */
        formatDate: function(date) {
            return date.getDate() + '/' + (date.getMonth() + 1) + '/' + date.getFullYear();
        },

        /**
         * @return {*|Boolean}
         */
        isCalculated: function() {
            return this.totals() && this.isFullMode() && quote.shippingMethod() != null; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*}
         */
        // getValue: function() {
        //     var setMaxDeliveryDates = this.setMaxDeliveryDates();
        //     // neu nhu chua chon address
        //     if (quote.shippingAddress() == null) {
        //         return 'Đang tính';
        //     }
        //     if (!this.isCalculated()) {
        //         return this.notCalculatedMessage;
        //     }
        //     var price = totals.getSegment('shipping').value;
        //     // console.log(price);
        //     if (price > 0) {
        //         return this.getFormattedPrice(price);
        //     }

        //     var method_code = this.getChottvnShippingMethodCode();

        //     if (method_code == "flatrate") {
        //         return 'Liên hệ';
        //     }
        //     if (method_code == "storepickupshipping") {
        //         return 'Miễn phí';
        //     }

        //     var flagShipping = this.flagShipping();
        //     // console.log(flagShipping);

        //     if (flagShipping == "over") {
        //         return 'Liên hệ';
        //     }
        //     if (flagShipping == "freeshipping") {
        //         return 'Miễn phí';
        //     }
        //     if (flagShipping == "accept" && price > 0) {
        //         return this.getFormattedPrice(price);
        //     }

        //     return 'Đang tính';
        // },

        /**
         * @return {*}
         */
        getValue: function() {
            var setMaxDeliveryDates = this.setMaxDeliveryDates(),
                price, flagShipping = totals.getSegment('shipping').area;

            if (flagShipping) {
                return flagShipping;
            }

            if (!this.isCalculated()) {
                return this.notCalculatedMessage;
            }
            price = totals.getSegment('shipping').value;

            return this.getFormattedPrice(price);
        },

        /**
         * @return {*}
         */
        isShowShippingNotification: function() {
            var shippingNotificationData = this.getShippingNotification();
            return shippingNotificationData.length > 0;
        },

        /**
         * @return {*}
         */
        getShippingNotification: function() {
            var data = window.shippingNotification;
            var decodeData = _.unescape(data);
            return decodeData;
        }
    });
});