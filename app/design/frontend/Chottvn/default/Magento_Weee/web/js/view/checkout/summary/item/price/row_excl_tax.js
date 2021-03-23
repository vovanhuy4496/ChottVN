/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Weee/js/view/checkout/summary/item/price/weee'
], function(weee) {
    'use strict';

    return weee.extend({
        defaults: {
            template: 'Magento_Weee/checkout/summary/item/price/row_excl_tax'
        },

        /**
         * @param {Object} item
         * @return {Number}
         */
        getFinalRowDisplayPriceExclTax: function(item) {
            var rowTotalExclTax = parseFloat(item['row_total']);

            if (!window.checkoutConfig.getIncludeWeeeFlag) {
                rowTotalExclTax += parseFloat(item['weee_tax_applied_amount']);
            }

            return rowTotalExclTax;
        },

        /**
         * @param {Object} item
         * @return {Number}
         */
        getRowDisplayPriceExclTax: function(item) {
            var rowTotalExclTax = parseFloat(item['row_total']);

            if (window.checkoutConfig.getIncludeWeeeFlag) {
                rowTotalExclTax += this.getRowWeeeTaxExclTax(item);
            }

            return rowTotalExclTax;
        },

        /**
         * @param {Object} item
         * @return {Number}
         */
        getRowWeeeTaxExclTax: function(item) {
            var totalWeeeTaxExclTaxApplied = 0,
                weeeTaxAppliedAmounts;

            if (item['weee_tax_applied']) {
                weeeTaxAppliedAmounts = JSON.parse(item['weee_tax_applied']);
                weeeTaxAppliedAmounts.forEach(function(weeeTaxAppliedAmount) {
                    totalWeeeTaxExclTaxApplied += parseFloat(Math.max(weeeTaxAppliedAmount['row_amount'], 0));
                });
            }

            return totalWeeeTaxExclTaxApplied;
        },


        /**
         * @param {Object} item
         * @return {Object} product
         */
        getProduct: function(item) {
            var product = undefined;
            if (window.checkoutConfig && window.checkoutConfig && window.checkoutConfig.quoteItemData) {
                window.checkoutConfig.quoteItemData.forEach(function(quoteItem) {
                    if (quoteItem.item_id == item.item_id) {
                        product = quoteItem.product;
                    }
                });
            }

            return product;
        },
        /**
         * @param {Object} item
         * @return {Bool}
         */
        isCatalogSpecialPrice: function(item) {
            var isSpecialPrice = false;
            var product = this.getProduct(item);
            if (product && parseFloat(product.price) != item.base_price) {
                isSpecialPrice = true;
            }
            return isSpecialPrice;
        },
        /**
         * @param {Object} item
         * @return {Float}
         */
        getPriceOriginal: function(item) {
            var price = undefined;
            var product = this.getProduct(item);
            if (product) {
                price = parseFloat(product.price);
            }
            return price;
        },
        /**
         * @param {Object} item
         * @return {Float}
         */
        getDiscountPercent: function(item) {
            var discountPercent = 0;
            var originalPrice = this.getPriceOriginal(item);
            if (originalPrice && originalPrice != 0) {
                discountPercent = 100 - Math.round((item.base_price / originalPrice) * 100);
            }
            return discountPercent;
        },
        /**
         * @param {Object} item
         * @return {String}
         */
        getDiscountPercentString: function(item) {
            return "-" + this.getDiscountPercent(item) + "%";
        },
        /**
         * @param {Object} item
         * @return {Float}
         */
        getTotalAmountOriginal: function(item) {
            var totalAmount = 0;
            var originalPrice = this.getPriceOriginal(item);
            if (originalPrice && item.qty) {
                totalAmount = originalPrice * parseFloat(item.qty);
            }
            return totalAmount;
        },
        checkPromotionOrGift: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);

            if (item.ampromo_rule_id) {
                return null;
            }

            return true;
        },
        getProductUnit: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);

            if (item['productUnit']) {
                return '/' + item['productUnit'];
            }
            return null;
        },
        getItem: function(item_id) {
            var itemElement = null;
            _.each(window.checkoutConfig.quoteItemData, function(element, index) {
                if (element.item_id == item_id) {
                    itemElement = element;
                }
            });
            return itemElement;
        },

    });
});