define(
    [
        'jquery',
        'mage/translate',
        'Amasty_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Customer/js/customer-data',
        'mage/url'
    ],
    function(
        $,
        $t,
        resourceUrlManager,
        totals,
        quote,
        storage,
        errorProcessor,
        shippingService,
        rateRegistry,
        paymentService,
        methodConverter,
        customerData,
        urlBuilder
    ) {
        "use strict";
        return function(itemId, itemName) {
            if (totals.isLoading())
                return;

            var url = window.location.origin + '/';

            totals.isLoading(true);
            $('.block.items-in-cart').trigger('processStart');
            shippingService.isLoading(true);
            var serviceUrl = resourceUrlManager.getUrlForRemoveItem(quote);
            var shipppingAddress = quote.shippingAddress();
            // var param_city_id = shipppingAddress['extension_attributes']['city_id'];
            //Fix for magento 2.2.2
            if (shipppingAddress.extensionAttributes &&
                shipppingAddress.extensionAttributes.checkoutFields &&
                Object.keys(shipppingAddress.extensionAttributes.checkoutFields).length === 0
            ) {
                shipppingAddress.extensionAttributes.checkoutFields = [];
            }
            // shipppingAddress.customAttributes = undefined;
            // shipppingAddress.extension_attributes = {};
            shipppingAddress.township = undefined;
            storage.post(
                serviceUrl, JSON.stringify({
                    itemId: itemId,
                    address: shipppingAddress
                }), false
            ).done(
                function(result) {
                    if (!result) {
                        return window.location.reload();
                    }

                    var itemIds = result.totals.items.map(function(value, index) {
                        return value.item_id;
                    });
                    customerData.reload(['cart']);

                    if (!itemIds.length) {
                        window.location.href = urlBuilder.build("checkout/cart/index");
                        return this;
                    }
                    window.checkoutConfig.quoteItemData = window.checkoutConfig.quoteItemData.filter(function(item) {
                        return itemIds.indexOf(+item.item_id) !== -1;
                    });
                    var ampromo_cart = '#' + 'ampromo_cart_' + itemId + '';
                    var _ampromo_cart = $(ampromo_cart);
                    _ampromo_cart.remove();
                    var message = $t('You have removed the product %1 from the cart').replace('%1', itemName); //eslint-disable-line max-len
                    if (Number($("#items-promo li").size()) == 0) {
                        $('#items-promo').remove();
                    }
                    $.toaster({ title: $t('Notice'), priority: 'success', message: message });
                    if (!$('.message-error-out-of-stock').hasClass('error-out-of-stock')) {
                        $('#place-order-trigger').prop('disabled', false);
                    }

                    shippingService.setShippingRates(result.shipping);
                    rateRegistry.set(quote.shippingAddress().getKey(), result.shipping);
                    quote.setTotals(result.totals);

                    paymentService.setPaymentMethods(methodConverter(result.payment));

                    $.ajax({
                        url: url + 'sales/promo/quoteitem',
                        data: {
                            type: 'showPromoItems'
                        },
                        type: 'POST',
                        dataType: 'json',
                        beforeSend: function() {},
                        success: function(data, status, xhr) {
                            $('#items-promo').hide();
                            // console.log(data);
                            if (data.html) {
                                $('#ampromo-spent .minicart-items-wrapper').html('');
                                $('#ampromo-spent .minicart-items-wrapper').html(data.html);
                                $('#ampromo-spent').removeClass('hide-ampromo-spent');
                                $('#ampromo-spent').addClass('show-ampromo-spent');
                            } else {
                                $('#ampromo-spent').addClass('hide-ampromo-spent');
                                $('#ampromo-spent').removeClass('show-ampromo-spent');
                            }
                        },
                        complete: function() {},
                        error: function(xhr, status, errorThrown) {
                            console.log(errorThrown);
                        }
                    });
                }
            ).fail(
                function(response) {
                    errorProcessor.process(response);
                }
            ).always(
                function() {
                    shippingService.isLoading(false);
                    totals.isLoading(false);
                    $('.block.items-in-cart').trigger('processStop');
                }
            );
        }
    }
);