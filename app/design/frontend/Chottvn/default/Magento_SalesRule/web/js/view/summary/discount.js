/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function($, Component, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_SalesRule/summary/discount'
        },
        totals: quote.getTotals(),

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function() {
            return this.isFullMode() && this.getPureValue() != 0; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*}
         */
        getCouponCode: function() {
            if (!this.totals()) {
                return null;
            }

            return this.totals()['coupon_code'];
        },

        /**
         * @return {*}
         */
        getCouponLabel: function() {
            if (!this.totals()) {
                return null;
            }

            return this.totals()['coupon_label'];
        },

        /**
         * Get discount title
         *
         * @returns {null|String}
         */
        getTitle: function() {
            var discountSegments;

            if (!this.totals()) {
                return null;
            }

            discountSegments = this.totals()['total_segments'].filter(function(segment) {
                return segment.code.indexOf('discount') !== -1;
            });

            return discountSegments.length ? 'Giảm giá:' : null;
        },

        /**
         * @return {Number}
         */
        getPureValue: function() {
            var price = 0;

            if (this.totals() && this.totals()['discount_amount']) {
                price = parseFloat(this.totals()['discount_amount']);
            }

            return price;
        },

        /**
         * @return {*|String}
         */
        getValue: function() {
            return this.getFormattedPrice(this.getPureValue());
        },

        showTotalRules: function() {
            if ($('.total-rules-cart').hasClass('show-total-rules')) {
                return $('tr.total-rules-cart').css('display', 'none'),
                    $('tr.totals.discount .title').addClass('-collapsed'),
                    $('.total-rules-cart').removeClass('show-total-rules'),
                    $('.total-rules-cart').addClass('hide-total-rules'), 1;
            }
            return $('tr.total-rules-cart').css('display', 'table-row'),
                $('tr.totals.discount .title').removeClass('-collapsed'),
                $('.total-rules-cart').addClass('show-total-rules'),
                $('.total-rules-cart').removeClass('hide-total-rules'), 1;
        }
    });
});