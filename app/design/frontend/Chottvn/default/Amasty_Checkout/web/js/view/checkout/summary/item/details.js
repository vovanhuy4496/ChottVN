/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/item/details',
        'Magento_Checkout/js/model/quote',
        'Magento_Ui/js/modal/confirm',
        'Amasty_Checkout/js/action/remove-item',
        'Amasty_Checkout/js/action/update-item',
        'mage/translate',
        'ko',
        'Amasty_Checkout/js/options/configurable',
        'priceOptions',
        'mage/validation',
        'Magento_Catalog/js/price-utils',
    ],
    function($, Component, quote, confirm, removeItemAction, updateItemAction, $t, ko, configurable, priceOptions, validation, priceUtils) {
        "use strict";
        var quoteItemData = window.checkoutConfig.quoteItemData;
        var detectMobile = window.detectMobile;
        var detectTablet = window.detectTablet;
        var url = window.location.origin + '/';

        return Component.extend({
            defaults: {
                template: 'Amasty_Checkout/checkout/summary/item/details'
            },
            quoteItemData: quoteItemData,
            detectMobile: detectMobile,
            detectTablet: detectTablet,
            url: url,

            getRatingSummary: function(quoteItem) {
                return null;
                if (quoteItem.extension_attributes) {
                    return null;
                }
                var reviewsCount = this.getReviewsCount(quoteItem),
                    item = this.getItem(quoteItem.item_id),
                    ratingSummary = item['ratingSummary'] ? item['ratingSummary'] : 0;
                var html =
                    '<div class="star-ratings-css"> \
                        <div class="star-ratings-css-top" style="width:' + ratingSummary + '%"><span><i class="fas fa-star"></i></span><span><i class="fas fa-star"></i></span><span><i class="fas fa-star"></i></span><span><i class="fas fa-star"></i></span><span><i class="fas fa-star"></i></span></div> \
                        <div class="star-ratings-css-bottom"><span><i class="fas fa-star"></i></span><span><i class="fas fa-star"></i></span><span><i class="fas fa-star"></i></span><span><i class="fas fa-star"></i></span><span><i class="fas fa-star"></i></span></div> \
                    </div> \
                    <div class="reviews-actions">' + reviewsCount + '</div>';

                return html;
            },

            countRuleNames: function(quoteItem) {
                var item = this.getItem(quoteItem.item_id);
                var ruleNames = this.getCondition(item, 'ruleNames');
                if (ruleNames && item['ruleNames'] && item['ruleNames'].length > 0) {
                    return true;
                }
                return null;
            },

            getRuleNames: function(quoteItem) {
                var item = this.getItem(quoteItem.item_id),
                    html = '<ul class="rules-list" id="rules-list-' + item['item_id'] + '">',
                    qty = quoteItem.qty;
                $.map(item['ruleNames'], function(element, key) {
                    var valueProductUnit = Number(item[element]['getCartPromoQty']) * Number(qty);
                    var getProductUnit = '(' + valueProductUnit + ' ' + item[element]['getProductUnit'] + ')';
                    html = html + '<li><i class="fas fa-gift"></i><span><span class="rule-name-promo">Quà Tặng: </span>' + element + ' ' + getProductUnit;
                    if (item[element]['hasProductPromoUrl']) {
                        html = html + '<a target="_blank" class="product-item-name product-item-detail" href="' + item[element]['getProductPromoUrl'] + '">' + 'Xem chi tiết &raquo;' + '</a>';
                    }
                    html = html + '</span></br>';
                    html = html + '</li>';
                });
                html = html + '</ul>'
                return html;
            },

            // getRuleNames: function(quoteItem) {
            //     var item = this.getItem(quoteItem.item_id),
            //         checked = '',
            //         namePrdSelected = '',
            //         idPromoItem = '',
            //         html = '<ul class="rules-list" id="rules-list-' + item['item_id'] + '">';
            //     // console.log(item);
            //     // item['ruleNames'].forEach(element => {
            //     $.map(item['ruleNames'], function(element, key) {
            //         var nameRule = (item['renameRule'] && item['renameRule'][key]) ? item['renameRule'][key] : element;
            //         var urlRule = (item['productUrlRule'] && item['productUrlRule'][key]) ? item['productUrlRule'][key] : '';

            //         html = html + '<li><i class="fas fa-gift"></i> <span>' + nameRule;

            //         if (item['ampromo_items'] && item['salesRuleIds']) {
            //             $.map(item['salesRuleIds'], function(valueSalesRule, keySalesRule) {
            //                 valueSalesRule = parseInt(valueSalesRule);
            //                 $.map(item['ampromo_items'][valueSalesRule], function(name, sku) {
            //                     if (element == name) {
            //                         if (sku && item['cart_promo_ids'] && item['getAmpromoRule']) {
            //                             var id = valueSalesRule;
            //                             var type = -1;

            //                             if (id) {
            //                                 type = item['getAmpromoRule'][id];
            //                             }
            //                             if (type != -1 && id && item[sku] && item[sku][id]) {
            //                                 // One of the SKUs below
            //                                 if (type == 1) {
            //                                     checked = item[sku][id]['checked'];
            //                                     idPromoItem = id;
            //                                     var id_radio = 'id="' + sku + '"';
            //                                     var cartPromoItemId = (typeof item[sku][id]['item_id'] == 'undefined') ? '' : item[sku][id]['item_id'];
            //                                     if (cartPromoItemId != '') {
            //                                         id_radio = 'id="' + item[sku][id]['item_id'] + '__' + sku + '"';
            //                                     }
            //                                     var oldCartPromoItemIds = (typeof item[sku][id]['oldCartPromoItemIds'] == 'undefined') ? '' : item[sku][id]['oldCartPromoItemIds'];
            //                                     if (checked == 'checked') {
            //                                         namePrdSelected = item[sku][id]['name'];
            //                                         id_radio = id_radio.replace(id_radio, id_radio + ' ' + 'checked="true"');
            //                                     }
            //                                     html = html + '<div style="display: none;" class="promo-items change-qty-promo">';
            //                                     html = html + '<input cartPromoParentId="' + item['product']['entity_id'] +
            //                                         '" cartPromoPrdId="' + item[sku][id]['entity_id'] +
            //                                         '" oldCartPromoItemIds="' + oldCartPromoItemIds +
            //                                         '" qtyPrdParent=inputQty_' + item['item_id'] +
            //                                         ' itemId="' + item['item_id'] +
            //                                         '" cartPromoPrdName="' + item[sku][id]['name'] +
            //                                         '" cartPromoOption="ampromo_items" cartPromoQty="' + item[sku][id]['discount_amount'] +
            //                                         '" class="radio promo_' + item['item_id'] + '" cartPromoIds="' + id +
            //                                         '" type="radio" name="' + item['item_id'] + '_' + id +
            //                                         '" productSku="' + sku + '" ' + id_radio + ' value="' + sku + '">';
            //                                     html = html + '<label class="label" for="' + sku + '">';
            //                                     html = html + '<span>' + item[sku][id]['name'] + '</span></label></div>';
            //                                     if (item[sku][id]['hasProductUrlRule'] && item[sku][id]['getProductUrl']) {
            //                                         html = html + '<div style="display: none;" class="promo-item-detail">' + '<a href="' + item[sku][id]['getProductUrl'] + '">Xem chi tiết &raquo;</a></div>';
            //                                     }
            //                                 }
            //                                 // All SKUs below
            //                                 if (type == 0) {
            //                                     html = html + '<div class="promo-items">';
            //                                     html = html + '<span><i class="fas fa-check-circle"></i>' + item[sku][id]['name'] + '</span></div>';
            //                                     if (item[sku][id]['hasProductUrlRule'] && item[sku][id]['getProductUrl']) {
            //                                         html = html + '<div class="promo-item-detail">' + '<a target="_blank" href="' + item[sku][id]['getProductUrl'] + '">Xem chi tiết &raquo;</a></div>';
            //                                     }
            //                                 }
            //                             }
            //                         }
            //                     }
            //                 });
            //             });
            //         }
            //         html = html + '</span>';
            //         if (urlRule != '') {
            //             html = html + '<span style="display: block;"><a target="_blank" href="' + urlRule + '">Xem chi tiết &raquo;</a></span>';
            //         }
            //         if (namePrdSelected != '') {
            //             html = html + '<div style="display: none;" id="' + item['item_id'] + '_' + idPromoItem + '" class="selected-gift-products-show"><span>' + namePrdSelected + '</span></div>';
            //         } else {
            //             html = html + '<div style="display: none;" id="' + item['item_id'] + '_' + idPromoItem + '" class=""><span></span></div>';
            //         }
            //         namePrdSelected = '';
            //         html = html + '</li>';

            //     });
            //     html = html + '</ul>'
            //     return html;
            // },

            // countRuleNames: function(quoteItem) {
            //     var item = this.getItem(quoteItem.item_id);
            //     var cart_price_rules_1_N = this.getCondition(item, 'cart_price_rules_1_N');
            //     var cartPriceRulesIds = this.getCondition(item, 'cartPriceRulesIds');
            //     var cartPriceRulesSku = this.getCondition(item, 'cartPriceRulesSku');
            //     if (cart_price_rules_1_N || cartPriceRulesIds || cartPriceRulesSku) {
            //         return true;
            //     }
            //     return null;
            // },

            // getRuleNames: function(quoteItem) {
            //     var item = this.getItem(quoteItem.item_id),
            //         html = '<ul class="rules-list">';

            //     var cartPriceRulesSku = this.getCondition(item, 'cartPriceRulesSku');
            //     if (cartPriceRulesSku) {
            //         $.map(item['cartPriceRulesSku'], function(element, key) {
            //             html = html + '<li><i class="fas fa-gift"></i> <span>' + key + '</span>';
            //             html = html + '<div class="selected-gift-products-show"><span>Quà Tặng: ' + element.productNamePromo + '</span></div>';
            //             if (element.hasProductPromoUrl) {
            //                 html = html + '<a target="_blank" class="product-item-name product-item-detail" href="' + element.getProductPromoUrl + '">' + 'Xem chi tiết &raquo;' + '</a>';
            //             }
            //             html = html + '</li>';
            //         });
            //     }

            //     var cartPriceRulesIds = this.getCondition(item, 'cartPriceRulesIds');
            //     if (cartPriceRulesIds) {
            //         $.map(item['cartPriceRulesIds'], function(elementIds, keyIds) {
            //             html = html + '<li><i class="fas fa-gift"></i> <span>' + elementIds + '</span>';
            //             html = html + '<div class="selected-gift-products-show">';
            //             html = html + '<span>Quà Tặng:</br></span>';
            //             $.map(item[elementIds], function(element, key) {
            //                 html = html + '<span>' + '- ' + element.productNamePromo + '</br></span>';
            //                 if (element.hasProductPromoUrl) {
            //                     html = html + '<a target="_blank" class="product-item-name product-item-detail" href="' + element.getProductPromoUrl + '">' + 'Xem chi tiết &raquo;' + '</a>';
            //                 }
            //             });
            //             html = html + '</div></li>';
            //         });
            //     }

            //     var cart_price_rules_1_N = this.getCondition(item, 'cart_price_rules_1_N');
            //     if (cart_price_rules_1_N) {
            //         $.map(item['cart_price_rules_1_N'], function(element, key) {
            //             html = html + '<li><i class="fas fa-gift"></i> <span>' + key + '</span>';
            //             html = html + '<div class="selected-gift-products-show"><span>Bạn đã chọn quà tặng: ' + element.productNamePromo + '</span></div>';
            //             if (element.hasProductPromoUrl) {
            //                 html = html + '<a target="_blank" class="product-item-name product-item-detail" href="' + element.getProductPromoUrl + '">' + 'Xem chi tiết &raquo;' + '</a>';
            //             }
            //             html = html + '</li>';
            //         });
            //     }

            //     html = html + '</ul>'
            //     return html;
            // },

            // cartPriceRules: function(quoteItem) {
            //     var item = this.getItem(quoteItem.item_id);
            //     var cartPriceRulesSku = this.getCondition(item, 'cartPriceRulesSku');
            //     // console.log(cartPriceRulesSku);
            //     var cartPriceRulesIds = this.getCondition(item, 'cartPriceRulesIds');
            //     // console.log(cartPriceRulesIds);
            //     if ($.cookie("htmlPromo")) {
            //         var html = $.cookie("htmlPromo");
            //     } else {
            //         var html = '';
            //     }
            //     // html = html + '<li class="product-item">';
            //     // html = html + '<div class="row product">';
            //     if (cartPriceRulesSku) {
            //         $.map(item['cartPriceRulesSku'], function(element, key) {
            //             // if ($.cookie("htmlPromo")) {
            //             //     html = $.cookie("htmlPromo");
            //             // }
            //             // console.log(html);
            //             html = html + '<li class="product-item">';
            //             html = html + '<div class="row product">';
            //             html = html + '<div class="col-xl-3 col-lg-12 col-md-12 col-sm-3 col-4 product-image-container">';
            //             if (element.hasProductPromoUrl) {
            //                 html = html + '<a target="_blank" class="product-image-wrapper" href="' + element.getProductPromoUrl + '">';
            //                 html = html + '<img src="' + element.imagePromo + '">';
            //                 html = html + '</a>';
            //             } else {
            //                 html = html + '<span class="product-image-wrapper">';
            //                 html = html + '<img src="' + element.imagePromo + '">';
            //                 html = html + '</span>';
            //             }
            //             html = html + '</div>';
            //             html = html + '<div class="col-xl-9 col-lg-12 col-md-12 col-sm-9 col-8 product-item-details">';
            //             html = html + '<span class="rule-name-promo">' + key + '</span>';
            //             html = html + '<div class="row product-item-inner">';
            //             if (element.hasProductPromoUrl) {
            //                 html = html + '<a target="_blank" class="product-item-name product-item-detail" href="' + element.getProductPromoUrl + '">' + element.productNamePromo + '</a>';
            //             } else {
            //                 html = html + '<span class="product-item-name product-item-detail">' + element.productNamePromo + '</span>';
            //             }
            //             html = html + '<div class="product-item-merge-span">';
            //             if (element.productBrandPromo) {
            //                 html = html + '<span class="product-item-detail">' + $t('Brands: ') + element.productBrandPromo + '</span>';
            //             }
            //             if (element.productModelPromo) {
            //                 html = html + '<span class="product-item-detail">' + $t('Model: ') + element.productModelPromo + '</span>';
            //             }
            //             html = html + '</div>';
            //             html = html + '</div>';
            //             html = html + '</div>';
            //             html = html + '</div>';
            //             html = html + '</li>';
            //         });
            //     }
            //     if (cartPriceRulesIds && cartPriceRulesIds.length > 0) {
            //         $.map(item['cartPriceRulesIds'], function(element, key) {
            //             // if ($.cookie("htmlPromo")) {
            //             //     html = $.cookie("htmlPromo");
            //             // }
            //             // console.log(html);
            //             html = html + '<li class="product-item">';
            //             html = html + '<div class="row product">';
            //             html = html + '<div class="col-xl-3 col-lg-12 col-md-12 col-sm-3 col-4 product-image-container">';
            //             if (item['cartPriceRulesSkus'][element].hasProductPromoUrl) {
            //                 html = html + '<a target="_blank" class="product-image-wrapper" href="' + item['cartPriceRulesSkus'][element].getProductPromoUrl + '">';
            //                 html = html + '<img src="' + item['cartPriceRulesSkus'][element].imagePromo + '">';
            //                 html = html + '</a>';
            //             } else {
            //                 html = html + '<span class="product-image-wrapper">';
            //                 html = html + '<img src="' + item['cartPriceRulesSkus'][element].imagePromo + '">';
            //                 html = html + '</span>';
            //             }
            //             html = html + '</div>';
            //             html = html + '<div class="col-xl-9 col-lg-12 col-md-12 col-sm-9 col-8 product-item-details">';
            //             html = html + '<span class="rule-name-promo">' + item['cartPriceRulesSkus'][element].ruleNamePromo + '</span>';
            //             html = html + '<div class="row product-item-inner">';
            //             if (item['cartPriceRulesSkus'][element].hasProductPromoUrl) {
            //                 html = html + '<a target="_blank" class="product-item-name product-item-detail" href="' + item['cartPriceRulesSkus'][element].getProductPromoUrl + '">' + item['cartPriceRulesSkus'][element].productNamePromo + '</a>';
            //             } else {
            //                 html = html + '<span class="product-item-name product-item-detail">' + item['cartPriceRulesSkus'][element].productNamePromo + '</span>';
            //             }
            //             html = html + '<div class="product-item-merge-span">';
            //             if (item['cartPriceRulesSkus'][element].productBrandPromo) {
            //                 html = html + '<span class="product-item-detail">' + $t('Brands: ') + item['cartPriceRulesSkus'][element].productBrandPromo + '</span>';
            //             }
            //             if (item['cartPriceRulesSkus'][element].productModelPromo) {
            //                 html = html + '<span class="product-item-detail">' + $t('Model: ') + item['cartPriceRulesSkus'][element].productModelPromo + '</span>';
            //             }
            //             html = html + '</div>';
            //             html = html + '</div>';
            //             html = html + '</div>';
            //             html = html + '</div>';
            //             html = html + '</li>';
            //         });
            //     }
            //     // html = html + '</div>';
            //     // html = html + '</li>';
            //     // console.log(html);
            //     if ($.cookie("htmlPromo") && html != '') {
            //         var htmlPromo = $.cookie("htmlPromo");
            //         console.log(htmlPromo);
            //         html = htmlPromo + html;
            //     }
            //     $.cookie("htmlPromo", html);
            //     var _html = $.cookie("htmlPromo");
            //     // console.log(_html);
            //     $('#content-item-promo').html(_html);

            //     return true;
            //     // $('#items-promo').hide();
            // },

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

            getOptions: function(quoteItem) {
                var item = this.getItem(quoteItem.item_id),
                    html = '';
                if (this.getDetectMobile()) {
                    html = html + '<div class="list-options-mobile">';
                }
                item['customOptionsConfig'].forEach(element => {
                    html = html + '<span class="white-space-option">' + element.label + ': ' + element.value + '</span>';
                });
                if (this.getDetectMobile()) {
                    html = html + '</div>';
                }
                return html;
            },

            getReviewsCount: function(quoteItem) {
                var item = this.getItem(quoteItem.item_id);

                if (item['reviewsCount'] > 0) {
                    return item['reviewsCount'] + ' ' + $t('number of reviews');
                }
                return $t('No review on product');
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

            getProductUnit: function(quoteItem) {
                var item = this.getItem(quoteItem.item_id);
                var productUnit = this.getCondition(item, 'productUnit');
                if (productUnit && item['productUnit']) {
                    return item['productUnit'];
                }
                return null;
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

            redirectDetail: function(quoteItem) {
                var redirectDetail_ = '#' + 'redirectDetail_' + quoteItem.item_id + '';
                var redirectDetail = $(redirectDetail_);
                if (typeof redirectDetail.attr('href') != 'undefined') {
                    // return window.location.replace(redirectDetail.attr('href'));
                    return window.open(redirectDetail.attr('href'), '_blank');
                }
                return true;
            },

            checkPromotionOrGift: function(quoteItem) {
                var item = this.getItem(quoteItem.item_id);
                var ampromo_rule_id = this.getCondition(item, 'ampromo_rule_id');

                if (ampromo_rule_id && item.ampromo_rule_id) {
                    return null;
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

            /**
             * @param item
             * @return {*}
             */
            getItemConfig: function(item) {
                return this.getPropertyDataFromItem(item, 'amcheckout');
            },

            /**
             *
             * @param item
             * @param propertyName
             * @return {*}
             */
            getPropertyDataFromItem: function(item, propertyName) {
                var property,
                    itemDetails;

                if (item.hasOwnProperty(propertyName)) {
                    property = item[propertyName];
                }

                var quoteItem = this.getItemFromQuote(item);

                if (quoteItem.hasOwnProperty(propertyName)) {
                    property = quoteItem[propertyName];
                }

                if (property) {
                    this.storage().set('item_details' + item.item_id + propertyName, property);

                    return property;
                }

                itemDetails = this.storage().get('item_details' + item.item_id + propertyName);

                return itemDetails ? itemDetails : false;
            },

            /**
             *
             * @param item
             * @return {*}
             */
            getStockStatusHtml: function(item) {
                return this.getPropertyDataFromItem(item, 'amstockstatus');
            },
            /**
             *
             * @param item
             * @return {*}
             */
            getItemFromQuote: function(item) {
                var items = quote.getItems();
                var quoteItems = items.filter(function(quoteItem) {
                    return quoteItem.item_id == item.item_id;
                });

                if (quoteItems.length == 0) {
                    return false;
                }

                return quoteItems[0];
            },

            getConfigurableOptions: function(item) {
                var itemConfig = this.getItemConfig(item);

                if (itemConfig.hasOwnProperty('configurableAttributes')) {
                    return itemConfig.configurableAttributes.template;
                }

                return '';
            },

            getCustomOptions: function(item) {
                var itemConfig = this.getItemConfig(item);

                if (itemConfig.hasOwnProperty('customOptions')) {
                    return itemConfig.customOptions.template;
                }

                return '';
            },

            initOptions: function(item) {
                var itemConfig = this.getItemConfig(item);

                var containerSelector = '[data-role="product-attributes"][data-item-id=' + item.item_id + ']';
                var container = $(containerSelector);

                if (itemConfig.hasOwnProperty('configurableAttributes')) {
                    container.amcheckoutConfigurable({
                        spConfig: JSON.parse(itemConfig.configurableAttributes.spConfig),
                        superSelector: containerSelector + ' .super-attribute-select'
                    });
                }

                if (itemConfig.hasOwnProperty('customOptions')) {
                    container.priceOptions({
                        optionConfig: JSON.parse(itemConfig.customOptions.optionConfig)
                    });
                }

                item.form = container;
                item.isUpdated = ko.observable(false);
                item.validation = container.validation();

                container.find('input, select, textarea').change(function() {
                    item.isUpdated(true);
                });
            },

            updateItem: function(item) {
                if (item.validation.valid()) {
                    updateItemAction(item, item.form.serialize());
                }
            },

            deleteItem: function(data) {
                var item = this.getItem(data.item_id);
                // console.log(item);
                removeItemAction(item.item_id, item.getNameShort);
                // confirm({
                //     content: $t("Are you sure you would like to remove this item from the shopping cart?"),
                //     actions: {
                //         confirm: function() {
                //             removeItemAction(item.item_id);
                //         },
                //         always: function(event) {
                //             event.stopImmediatePropagation();
                //         }
                //     }
                // });
            },

            canShowDeleteButton: function() {
                return quote.getItems().length >= 1;
            },

            minusItem: function(data, parent) {
                var inputQty = '#' + 'inputQty_' + data.item_id + '';
                var input = $(inputQty);
                var value = parseInt(input.val());
                var finalValue = value - 1;
                var defaultStockQty = this.defaultStock(data),
                    qty = data.qty;
                if (finalValue >= 1) {
                    input.val(finalValue);
                    // console.log(finalValue);
                    if (Number(finalValue) > Number(defaultStockQty)) {
                        this.updateItem(data);
                    } else {
                        input.trigger("change");
                    }
                }
            },

            plusItem: function(data, parent) {
                var inputQty = '#' + 'inputQty_' + data.item_id + '';
                var input = $(inputQty);
                var value = parseInt(input.val());
                var finalValue = value + 1;
                input.val(finalValue);
                input.trigger("change");
            },

            changedQty: function(data, parent) {
                var inputQty = '#' + 'inputQty_' + data.item_id + '';
                var input = $(inputQty);
                // var checkQtyOver = this.checkOverDefaultStock(data);
                // var showMessOver_ = '#' + 'showMessOver_' + data.item_id + '';
                // var showMessOver = $(showMessOver_);
                // var showMessOutOfStock_ = '#' + 'showMessOutOfStock_' + data.item_id + '';
                // var showMessOutOfStock = $(showMessOutOfStock_);

                // // console.log(checkQtyOver);
                // if (checkQtyOver != null) {
                //     input.val(data.qty);
                //     $(showMessOver).html('');
                //     $(showMessOutOfStock).html('');
                //     $(showMessOver).html(checkQtyOver);
                //     return this;
                // }
                // $(showMessOver).html(checkQtyOver);

                var currentQty = input.val();
                if (currentQty > 0) {
                    this.updateItem(data);
                } else {
                    input.val(data.qty);
                }
            },

            enterChangedQty: function(d, e) {
                var input = $(e.target);
                if (e.keyCode === 13) {
                    if (input.val() != input.attr('oldqty')) {
                        var value = parseInt(input.val());
                        input.val(value);
                        input.trigger("change");
                    }
                    return false;
                }
                return true;
            },

            disableQuantityMinus: function(quoteItem) {
                var qty = quoteItem.qty,
                    defaultStockQty = this.defaultStock(quoteItem);

                if (qty > 1 && Number(defaultStockQty) > 0) {
                    return true;
                }
                return null;
            },

            disableQuantityPlus: function(quoteItem) {
                var qty = quoteItem.qty,
                    defaultStockQty = this.defaultStock(quoteItem);

                if (Number(qty) < Number(defaultStockQty)) {
                    return true;
                }
                return null;
            },

            disableQuantityInput: function(quoteItem) {
                var defaultStockQty = this.defaultStock(quoteItem);

                if (Number(defaultStockQty) == 0) {
                    return true;
                }
                return null;
            },

            defaultStock: function(quoteItem) {
                var item = this.getItem(quoteItem.item_id);
                var defaultStock = this.getCondition(item, 'defaultStock');
                if (defaultStock && item['defaultStock']) {
                    return item['defaultStock'];
                }
                return 0;
            },

            checkOverDefaultStock: function(quoteItem) {
                var defaultStockQty = this.defaultStock(quoteItem),
                    inputQty = '#' + 'inputQty_' + quoteItem.item_id + '',
                    input = $(inputQty),
                    currentQty = input.val(),
                    html = '';

                if (Number(defaultStockQty) > 0 && (Number(currentQty) > Number(defaultStockQty))) {
                    html += '<label class="label error message">';
                    html += $t('Chỉ còn ' + defaultStockQty + ' sản phẩm');
                    html += '</label>';
                }
                if (Number(defaultStockQty) == 0) {
                    html += '<label class="label error message">';
                    html += $t('Out of stock');
                    html += '</label>';
                }
                if (html) {
                    return html;
                }
                return null;
            },

            // check default stock khi load page
            checkOutofStock: function(quoteItem) {
                var defaultStockQty = this.defaultStock(quoteItem),
                    html = '',
                    qty = quoteItem.qty;

                if (Number(defaultStockQty) == 0) {
                    $.cookie("checkOutofStock", true);
                    html += '<label class="label error message error-out-of-stock">';
                    html += $t('Out of stock');
                    html += '</label>';
                }
                if (Number(defaultStockQty) > 0 && (Number(qty) > Number(defaultStockQty))) {
                    $.cookie("checkOutofStock", true);
                    html += '<label class="label error message">';
                    html += $t('Chỉ còn ' + defaultStockQty + ' sản phẩm');
                    html += '</label>';
                }
                if ($.cookie("checkOutofStock")) {
                    $('#place-order-trigger').prop('disabled', false);
                    $('#place-order-trigger').prop('disabled', true);
                }
                $.cookie("checkOutofStock", null);

                if (html) {
                    return html;
                }
                // $('#place-order-trigger').prop('disabled', false);

                return null;
            },

            // isDisabled: function(data, parent) {
            //     var quoteItem = this.getItemFromQuote(data);
            //     var inputQty = '#' + 'inputQty_' + data.item_id + '';
            //     var input = $(inputQty);
            //     var currentQty = input.val();
            //     console.log(currentQty);

            //     if (currentQty == 1) {
            //         return true;
            //     }
            //     return false;
            //     console.log(currentQty);
            //     if (quoteItem.ampromo_rule_id) {
            //         var disableDelete_ = '#' + 'disableDelete_' + data.item_id + '';
            //         var _disableDelete = $(disableDelete_);
            //         _disableDelete.addClass('pointer-events');

            //         var disableQty_ = '#' + 'disableQty_' + data.item_id + '';
            //         var _disableQty = $(disableQty_);
            //         _disableQty.children().addClass('pointer-events');
            //     }
            // },

            /**
             * @param {Object} item
             * @return {Object} product
             */
            getProductCTT: function(item) {
                var product = undefined;
                if (quoteItemData) {
                    quoteItemData.forEach(function(quoteItem) {
                        if (quoteItem.item_id == item.item_id) {
                            product = quoteItem.product;
                        }
                    });
                }

                return product;
            },
            /**
             * @param {Object} item
             * @return {Float}
             */
            getPriceCTT: function(item) {
                var price = undefined;
                var product = this.getProductCTT(item);
                if (product) {
                    price = parseFloat(product.price);
                }
                return this.getFormattedPrice(price);
            },

            /**
             * @param {Object} item
             * @return {Float}
             */
            getPriceOriginalCTT: function(item) {
                var price = undefined;
                var product = this.getProductCTT(item);
                if (product) {
                    price = parseFloat(product.price);
                }
                return price;
            },

            /**
             * @param {Object} item
             * @return {Number}
             */
            getRowDisplayPriceExclTax: function(item) {
                var product = this.getItem(item.item_id),
                    price = 0;
                var ampromo_rule_id = this.getCondition(product, 'ampromo_rule_id');
                var price = this.getCondition(product, 'price');
                // neu la qua tang
                if (ampromo_rule_id && product.ampromo_rule_id) {
                    if (product.base_original_price) {
                        var getDiscountPercentCTT = this.getDiscountPercentCTT(item);
                        if (getDiscountPercentCTT == null) {
                            return null;
                        }
                        if (getDiscountPercentCTT == 100) {
                            return this.getFormattedPrice(price);
                        }
                        price = (product.base_original_price * getDiscountPercentCTT) / 100;
                        price = product.base_original_price - price;
                        price = parseFloat(price);
                    }
                    if (price > 0) {
                        return this.getFormattedPrice(price);
                    }
                }
                if (price && product.price) {
                    price = parseFloat(product.price);
                }
                if (price > 0) {
                    return this.getFormattedPrice(price);
                }
                return null;
                // var price = undefined;
                // var product = this.getProductCTT(item);
                // if (product) {
                //     price = parseFloat(product.quote_item_price);
                // }
                // return this.getFormattedPrice(price);
            },

            /**
             * @param {Object} item
             * @return {Float}
             */
            getDiscountPercentCTT: function(item) {
                var discountPercent = 0;
                var originalPrice = this.getPriceOriginalCTT(item);
                if (originalPrice && originalPrice != 0) {
                    discountPercent = 100 - Math.round((item.base_price / originalPrice) * 100);
                }
                if (discountPercent > 0) {
                    return discountPercent;
                }
                return null;
            },
            /**
             * @param {Object} item
             * @return {String}
             */
            getDiscountPercentStringCTT: function(item) {
                return "-" + this.getDiscountPercentCTT(item) + "%";
            },

            /**
             * @param {*} price
             * @return {*|String}
             */
            getFormattedPrice: function(price) {
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },

            getDetectMobile: function() {
                // neu la mobile
                if (this.detectMobile > 0 && this.detectTablet == 0) {
                    return true;
                }
                return false;
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
    }
);
``