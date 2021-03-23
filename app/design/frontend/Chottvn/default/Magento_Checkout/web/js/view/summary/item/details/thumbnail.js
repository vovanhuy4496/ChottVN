/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery', 'uiComponent', 'mage/translate'], function($, Component, $t) {
    'use strict';

    var imageData = window.checkoutConfig.imageData;
    var imageDataCTT = window.imageDataCTT;
    var detectMobile = window.detectMobile;
    var detectTablet = window.detectTablet;
    var quoteItemData = window.checkoutConfig.quoteItemData;
    var url = window.location.origin + '/';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details/thumbnail'
        },
        displayArea: 'before_details',
        imageData: imageData,
        imageDataCTT: imageDataCTT,
        detectMobile: detectMobile,
        detectTablet: detectTablet,
        quoteItemData: quoteItemData,
        url: url,

        /**
         * @param {Object} item
         * @return {Array}
         */
        getImageItem: function(item) {
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']];
            }

            return [];
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getSrc: function(item) {
            var quoteItem = this.getItem(item.item_id);
            var image_url = this.getCondition(quoteItem, 'image_url');
            if (image_url && quoteItem.image_url) {
                return quoteItem.image_url;
            }
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].src;
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getWidth: function(item) {
            if (this.imageDataCTT.width) {
                return this.imageDataCTT.width;
            }
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].width;
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getHeight: function(item) {
            if (this.imageDataCTT.height) {
                return this.imageDataCTT.height;
            }
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].height;
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getWidthCTT: function(item) {
            if (this.imageDataCTT.width) {
                // console.log(parseInt(this.imageDataCTT.width));
                return parseInt(this.imageDataCTT.width) > 0 ? this.imageDataCTT.width + 'px' : this.imageDataCTT.width;
            }
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].width;
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getHeightCTT: function(item) {
            if (this.imageDataCTT.height) {
                // console.log(parseInt(this.imageDataCTT.height));
                return parseInt(this.imageDataCTT.height) > 0 ? this.imageDataCTT.height + 'px' : this.imageDataCTT.height;
            }
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].height;
            }

            return null;
        },

        /**
         * @param {Object} item
         * @return {null}
         */
        getAlt: function(item) {
            if (this.imageData[item['item_id']]) {
                return this.imageData[item['item_id']].alt;
            }

            return null;
        },

        getDetectMobile: function() {
            // neu la mobile
            if (this.detectMobile > 0 && this.detectTablet == 0) {
                return true;
            }
            return null;
        },

        getModel: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            var getModel = this.getCondition(item, 'getModel');
            if (getModel && item['getModel']) {
                return $t('Model: ') + item['getModel'];
            }
            return null;
        },

        getProductBrand: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            var productBrand = this.getCondition(item, 'productBrand');
            if (productBrand && item['productBrand']) {
                return $t('Brands: ') + item['productBrand'];
            }
            return null;
        },

        getGuarantee: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            var guarantee = this.getCondition(item, 'guarantee');
            if (guarantee && item['guarantee']) {
                return $t('Guarantee information') + ': ' + item['guarantee'];
            }
            return null;
        },

        getOptions: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id),
                html = '';
            // if (this.getDetectMobile()) {
            //     html = html + '<div class="list-options-mobile">';
            // }
            item['customOptionsConfig'].forEach(element => {
                html = html + '<span class="white-space-option">' + element.label + ': ' + element.value + '</span>';
            });
            // if (this.getDetectMobile()) {
            //     html = html + '</div>';
            // }
            return html;
        },

        getUrlPrd: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            var hasProductUrl = this.getCondition(item, 'hasProductUrl');
            if (hasProductUrl && item['hasProductUrl']) {

                if (item.product.url_key) {
                    if (item.product.type_id == 'configurable' && item['attributes']) {
                        var params = '';
                        $.each(item['attributes'], function(index, value) {
                            params = params + index + '=' + value + '&';
                        });
                        params = params.substring(0, params.length - 1);
                        // console.log(params.substring(0, params.length - 1));
                        return item['getProductUrl'] + '#' + params;
                        // if (item.jacket) {
                        //     return url + item.product.url_key + '.html' + '#jacket_color=' + item.jacket.jacket_color + '&jacket_size=' + item.jacket.jacket_size;
                        // }
                    }
                    return item['getProductUrl'];
                }
            }
            return null;
        },

        redirectImgDetail: function(quoteItem) {
            var redirectImgDetail_ = '#' + 'redirectImgDetail_' + quoteItem.item_id + '';
            var redirectImgDetail = $(redirectImgDetail_);
            if (typeof redirectImgDetail.attr('href') != 'undefined') {
                // return window.location.replace(redirectImgDetail.attr('href'));
                return window.open(redirectImgDetail.attr('href'), '_blank');
            }
            return true;
        },

        getItem: function(item_id) {
            var itemElement = null;
            _.each(this.quoteItemData, function(element, index) {
                if (element.item_id == item_id) {
                    itemElement = element;
                }
            });
            return itemElement;
        },

        redirectDetail: function(quoteItem) {
            var redirectDetail_ = '#' + 'redirectDetail_' + quoteItem.item_id + '';
            var redirectDetail = $(redirectDetail_);
            if (typeof redirectDetail.attr('href') != 'undefined') {
                return window.open(redirectDetail.attr('href'), '_blank');
                // return window.location.replace(redirectDetail.attr('href'));
            }
            return true;
        },

        getNamePrd: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            var ampromo_rule_id = this.getCondition(item, 'ampromo_rule_id');
            var product = this.getCondition(item, 'product');
            var getNameLongHtml = this.getCondition(item, 'getNameLongHtml');
            if (ampromo_rule_id && item.ampromo_rule_id) {
                return window.messages_prefix + (getNameLongHtml ? item['getNameLongHtml'] : '');
            }
            if (product && item.product.type_id == 'configurable' && getNameLongHtml) {
                var getNameShort = $.trim(item['getNameShort']);
                var nameLong = item['getNameLongHtml'];
                var namePrd = nameLong.replace('<strong>', '');
                namePrd = namePrd.replace('</strong>', '');
                namePrd = namePrd.replace(getNameShort, '<strong>' + getNameShort + '</strong>');
                return namePrd;
            }
            if (getNameLongHtml) {
                return item['getNameLongHtml'];
            }
        },

        checkPromoItems: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            // console.log(item);
            var ampromo_rule_id = this.getCondition(item, 'ampromo_rule_id');
            if (ampromo_rule_id && item['ampromo_rule_id'] && item['applied_rule_ids']) {
                var ampromo_rule_id = item['ampromo_rule_id'];
                var applied_rule_ids = item['applied_rule_ids'];
                if (applied_rule_ids.indexOf(ampromo_rule_id) != -1) {
                    // console.log(ampromo_rule_id + " found");
                    return false;
                }
            }
            var cart_promo_option = this.getCondition(item, 'cart_promo_option');

            if (cart_promo_option && item['cart_promo_option'] && (item['cart_promo_option'] == "ampromo_items" || item['cart_promo_option'] == "ampromo_cart" || item['cart_promo_option'] == "ampromo_spent")) {
                return false;
            }

            var checkPromoItems = this.getCondition(item, 'checkPromoItems');

            if ((checkPromoItems && item['checkPromoItems']) && (item['checkPromoItems'] == "ampromo_cart" || item['checkPromoItems'] == "virtual")) {
                return false;
            }

            if (item == null) {
                return false;
            }
            // console.log(item);
            return true;
        },

        getCondition: function(item, condition) {
            var value = '';
            for (var key in item) {
                if (key == condition && item[key]) {
                    value = item[key];
                    break;
                }
            }
            return value;
        }
    });
});