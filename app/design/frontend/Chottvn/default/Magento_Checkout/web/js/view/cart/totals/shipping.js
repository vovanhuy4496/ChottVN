/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/summary/shipping',
    'Magento_Checkout/js/model/quote',
    'jquery'
], function(Component, quote, $) {
    'use strict';

    return Component.extend({

        /**
         * @return {*}
         */
        getValueRaw: function() {
            var price;

            if (!this.isCalculated()) {
                return this.notCalculatedMessage;
            }
            price = this.totals()['shipping_amount'];

            return price;
        },

        /**
         * @return {*}
         */
        getChottvnShippingMethodCode: function() {
            var shippingMethod = '';

            if (!this.isCalculated()) {
                return '';
            }
            shippingMethod = quote.shippingMethod();

            return shippingMethod['method_code'] ? shippingMethod['method_code'] : shippingMethod['carrier_code'];
        },

        /**
         * @return over, accept
         */
        getBooleanChottvnHandlingOverWeight: function() {
            if (!this.isCalculated()) {
                return false;
            }
            var overWeightValue = window.overWeightValue;
            if (overWeightValue == undefined) {
                overWeightValue = 0;
            }
            var shipping_amount = quote.totals()['shipping_amount'];
            var total_weight = 0;
            var products = quote.getItems();
            var i;
            for (i = 0; i < products.length; i++) {
                if (products[i].weight != null && products[i].ampromo_rule_id == null) {
                    total_weight = Number(total_weight) + Number(products[i].weight) * Number(products[i].qty);
                }
            }
            // console.log(total_weight);

            // if free shipping , will don't use over weight 
            if (total_weight >= Number(overWeightValue) && shipping_amount > 0) {
                // set value in fee_shipping_contact
                return "over";
            } else {
                // set value in fee_shipping_contact
                return "accept";
            }
        },

        /**
         * @override
         */
        isCalculated: function() {
            return !!quote.shippingMethod();
        }
    });
});