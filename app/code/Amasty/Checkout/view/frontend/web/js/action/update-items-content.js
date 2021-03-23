define(
    [
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/error-processor',
        'uiRegistry',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'jquery',
    ],
    function(
        totals,
        errorProcessor,
        registry,
        quote,
        storage,
        $
    ) {
        "use strict";

        return function(quoteTotals) {
            if (totals.isLoading()) {
                return;
            }
            var url = window.location.origin + '/';

            totals.isLoading(true);

            storage.get('/checkout/ajax/getItemsData').done(
                function(result) {
                    if (!result) {
                        window.location.reload();
                    }

                    if (result.image_data) {
                        registry.get('checkout.sidebar.summary.cart_items.details.thumbnail').imageData = result.image_data;
                    }

                    if (result.options_data) {
                        var options = result.options_data;
                        quoteTotals.items.forEach(function(item) {
                            item.amcheckout = options[item.item_id];
                        });
                    }
                    quote.setTotals(quoteTotals);
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
                    totals.isLoading(false);
                }
            );
        }
    }
);