/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Weee/js/view/checkout/summary/item/price/weee'
], function (weee) {
    'use strict';

    return weee.extend({
        defaults: {
            template: 'Magento_Weee/checkout/summary/item/price/row_incl_tax',
            displayArea: 'row_incl_tax'
        },

        /**
         * @param {Object} item
         * @return {Number}
         */
        getFinalRowDisplayPriceInclTax: function (item) {
            var rowTotalInclTax = parseFloat(item['row_total_incl_tax']);

            if (!window.checkoutConfig.getIncludeWeeeFlag) {
                rowTotalInclTax += this.getRowWeeeTaxInclTax(item);
            }

            return rowTotalInclTax;
        },

        /**
         * @param {Object} item
         * @return {Number}
         */
        getRowDisplayPriceInclTax: function (item) {
            var rowTotalInclTax = parseFloat(item['row_total_incl_tax']);

            if (window.checkoutConfig.getIncludeWeeeFlag) {
                rowTotalInclTax += this.getRowWeeeTaxInclTax(item);
            }

            return rowTotalInclTax;
        },

        /**
         * @param {Object}item
         * @return {Number}
         */
        getRowWeeeTaxInclTax: function (item) {
            var totalWeeeTaxInclTaxApplied = 0,
                weeeTaxAppliedAmounts;

            if (item['weee_tax_applied']) {
                weeeTaxAppliedAmounts = JSON.parse(item['weee_tax_applied']);
                weeeTaxAppliedAmounts.forEach(function (weeeTaxAppliedAmount) {
                    totalWeeeTaxInclTaxApplied += parseFloat(Math.max(weeeTaxAppliedAmount['row_amount_incl_tax'], 0));
                });
            }

            return totalWeeeTaxInclTaxApplied;
        },


         /**
         * @param {Object} item
         * @return {Object} product
         */
        getProduct: function (item) {
            var product = undefined;
            if (window.checkoutConfig && window.checkoutConfig && window.checkoutConfig.quoteItemData){
                window.checkoutConfig.quoteItemData.forEach(function(quoteItem){
                    if (quoteItem.item_id == item.item_id){
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
        isCatalogSpecialPrice: function (item) {
            var isSpecialPrice = false;
            var product = this.getProduct(item); 
            if (product && parseFloat(product.price) != item.base_price){
                isSpecialPrice = true;
            }    
            return isSpecialPrice;
        },
        /**
         * @param {Object} item
         * @return {Float}
         */
        getPriceOriginal: function (item) {
            var price = undefined;
            var product = this.getProduct(item); 
            if (product){
                price = parseFloat(product.price);
            }    
            return price;
        },
        /**
         * @param {Object} item
         * @return {Float}
         */
        getDiscountPercent: function (item) {
            var discountPercent = 0;
            var originalPrice = this.getPriceOriginal(item); 
            if (originalPrice && originalPrice != 0){
                discountPercent = 100 - Math.round((item.base_price / originalPrice ) * 100);
            }    
            return discountPercent;
        },
        /**
         * @param {Object} item
         * @return {String}
         */
        getDiscountPercentString: function (item) {              
            return "-"+ this.getDiscountPercent(item) + "%";
        },
        /**
         * @param {Object} item
         * @return {Float}
         */
        getTotalAmountOriginal: function (item) {
            var totalAmount = 0;
            var originalPrice = this.getPriceOriginal(item); 
            if (originalPrice && item.qty){
                totalAmount = originalPrice * parseFloat(item.qty);
            }    
            return totalAmount;
        },

    });
});
