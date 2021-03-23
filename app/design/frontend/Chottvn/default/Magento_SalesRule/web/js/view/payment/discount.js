/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/action/cancel-coupon',
    'mage/translate'
], function($, ko, Component, quote, setCouponCodeAction, cancelCouponAction, $t) {
    'use strict';

    var totals = quote.getTotals(),
        couponCode = ko.observable(null),
        isApplied;
    var detectMobile = window.detectMobile;
    var detectTablet = window.detectTablet;
    var placeholderDiscountCode = (detectMobile > 0 && detectTablet == 0) ? $t('Nhập mã giảm giá/KM') : 'Nhập mã giảm giá/khuyến mãi';

    if (totals()) {
        couponCode(totals()['coupon_code']);
    }
    isApplied = ko.observable(couponCode() != null);

    return Component.extend({
        defaults: {
            template: 'Magento_SalesRule/payment/discount'
        },
        detectMobile: detectMobile,
        detectTablet: detectTablet,
        couponCode: couponCode,
        hideShowDiscount: ko.observable(false),
        placeholderDiscountCode: placeholderDiscountCode,

        /**
         * Applied flag
         */
        isApplied: isApplied,

        /** @inheritdoc */
        initObservable: function() {
            this._super();
            this.hideShowDiscount = ko.computed(function() {
                var hideShowDiscount = false;

                // if (totals()['coupon_code']) {
                //     $('#discount-code').css('min-width', '190px');
                //     hideShowDiscount = true;
                // } else {
                //     $('#discount-code').css('min-width', '215px');
                // }
                return hideShowDiscount;
            }, this);

            return this;
        },

        /**
         * Coupon code application procedure
         */
        apply: function() {
            if (this.validate()) {
                setCouponCodeAction(couponCode(), isApplied);
            }
        },

        /**
         * Cancel using coupon
         */
        cancel: function() {
            if (this.validate()) {
                couponCode('');
                cancelCouponAction(isApplied);
            }
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function() {
            var form = '#discount-form';

            return $(form).validation() && $(form).validation('isValid');
        },

        applyDiscountCode: function() {
            // if (this.hideShowDiscount()) {
            //     this.hideShowDiscount(false);
            // } else {
            //     this.hideShowDiscount(true);
            // }
            if ($('div.payment-option-content').hasClass('show-discount')) {
                return $('div.payment-option-content').removeClass('show-discount'),
                    $('div.payment-option-content').addClass('hide-discount'), 1;
            }
            return $('div.payment-option-content').addClass('show-discount'),
                $('div.payment-option-content').removeClass('hide-discount'), 1;
        },

        upperCouponCode: function(target, option) {
            couponCode($('#discount-code').val().toUpperCase());
        },

        getDetectMobile: function() {
            // neu la mobile
            if (this.detectMobile > 0 && this.detectTablet == 0) {
                return true;
            }
            return false;
        }
    });
});