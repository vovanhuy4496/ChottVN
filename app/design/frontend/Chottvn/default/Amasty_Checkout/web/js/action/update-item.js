define(
    [
        'jquery',
        'Amasty_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
        'Magento_Checkout/js/model/shipping-rate-processor/customer-address',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Customer/js/customer-data',
        'Amasty_Checkout/js/action/update-items-content',
        'mage/translate',
    ],
    function(
        $,
        resourceUrlManager,
        totals,
        quote,
        storage,
        errorProcessor,
        rateRegistry,
        defaultProcessor,
        customerAddressProcessor,
        paymentService,
        methodConverter,
        customerData,
        updateItemsContent,
        $t
    ) {
        "use strict";

        return function(item, formData) {
            if (totals.isLoading())
                return;

            totals.isLoading(true);
            $('.block.items-in-cart').trigger('processStart');
            var serviceUrl = resourceUrlManager.getUrlForUpdateItem(quote);
            var inputQty = '#' + 'inputQty_' + item.item_id + '';
            var _inputQty = $(inputQty);
            var url = window.location.origin + '/';

            $.ajax({
                url: url + 'sales/promo/quoteitem',
                data: {
                    itemId: item.item_id,
                    formData: formData,
                    type: 'checkDefaultStock'
                },
                type: 'POST',
                dataType: 'json',
                beforeSend: function() {},
                success: function(data, status, xhr) {
                    // console.log(data);
                    if (data.status == false && data.defaultStockQty && data.defaultStockQty.error_code) {
                        _inputQty.val(_inputQty.attr('oldQty'));
                        totals.isLoading(false);
                        $('.block.items-in-cart').trigger('processStop');

                        // remove mess current
                        var showMessOver_ = '#' + 'showMessOver_' + item.item_id + '';
                        var showMessOver = $(showMessOver_);
                        var showMessOutOfStock_ = '#' + 'showMessOutOfStock_' + item.item_id + '';
                        var showMessOutOfStock = $(showMessOutOfStock_);
                        $(showMessOver).html('');
                        $(showMessOutOfStock).html('');

                        switch (data.defaultStockQty.error_code) {
                            case 'contact-us':
                                var html = '';
                                html += '<label class="label error message">';
                                html += data.defaultStockQty.messages;
                                html += '</label>';
                                $(showMessOver).html(html);
                                break;
                            case 'out-of-stock':
                                var html = '';
                                html += '<label class="label error message">';
                                html += data.defaultStockQty.messages;
                                html += '</label>';
                                $(showMessOutOfStock).html(html);
                                break;
                            case 'contact-us-promo':
                                $.toaster({ title: $t('Notice'), priority: 'danger', message: data.defaultStockQty.messages });
                                break;
                            case 'out-of-stock-promo':
                                $.toaster({ title: $t('Notice'), priority: 'danger', message: data.defaultStockQty.messages });
                                break;
                            default:
                        }
                    } else {
                        storage.post(
                            serviceUrl, JSON.stringify({
                                itemId: item.item_id,
                                formData: formData
                            }), false
                        ).done(
                            function(result) {
                                if (!result) {
                                    window.location.reload();
                                }

                                rateRegistry.set(quote.shippingAddress().getCacheKey(), null);
                                var type = quote.shippingAddress().getType();

                                if (type === 'customer-address') {
                                    customerAddressProcessor.getRates(quote.shippingAddress());
                                } else {
                                    defaultProcessor.getRates(quote.shippingAddress());
                                }

                                paymentService.setPaymentMethods(methodConverter(result.payment));
                                customerData.reload(['cart']);
                                totals.isLoading(false);
                                $('.block.items-in-cart').trigger('processStop');
                                updateItemsContent(result.totals);
                                _inputQty.attr('oldQty', item.qty);
                            }
                        ).fail(
                            function(response) {
                                _inputQty.val(_inputQty.attr('oldQty'));

                                errorProcessor.process(response);
                                totals.isLoading(false);
                                $('.block.items-in-cart').trigger('processStop');
                            }
                        );
                    }
                },
                complete: function() {},
                error: function(xhr, status, errorThrown) {
                    console.log(errorThrown);
                }
            });
        }
    }
);