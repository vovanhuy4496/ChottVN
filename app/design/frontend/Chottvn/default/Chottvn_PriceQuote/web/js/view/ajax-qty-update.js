define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/translate',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/quote'
],
//function ($, getTotalsAction, customerData) {
function($, customerData, $t, selectShippingAddressAction, quote) {
    $.fn.addressCheckoutAddressUpdate = function() {
        if($('input[name="address-customer"]:checked').val()){
            address_customer = JSON.parse($('input[name="address-customer"]:checked').val());
        }else{
            var address_customer = '';
        }
        
        var mage_cache_storage = JSON.parse(localStorage.getItem("mage-cache-storage"));
        if(mage_cache_storage !== null && address_customer){
            if(typeof mage_cache_storage['checkout-data'] !== 'undefined'){
                var checkout_data = mage_cache_storage['checkout-data'];
                if(typeof address_customer.entity_id != 'undefined'){
                    // set select option checkout
                    checkout_data['selectedShippingAddress'] = 'customer-address'+address_customer.entity_id;
                }else{
                    // set select option checkout with new address
                    checkout_data['selectedShippingAddress'] = "new-customer-address";
                    var newCustomerShippingAddress = {
                                                        save_in_address_book: 1,
                                                        country_id: "VN",
                                                        city_id: address_customer.city_id,
                                                        city: address_customer.city,
                                                        region_id: address_customer.region_id,
                                                        region: address_customer.region,
                                                        township_id: address_customer.township_id,
                                                        township: address_customer.township,
                                                        street: {0: address_customer.street}
                                                    };
                    checkout_data['newCustomerShippingAddress'] = newCustomerShippingAddress;

                    // update data shippingAddressFromData
                    checkout_data['shippingAddressFromData']['country_id'] = "VN";
                    checkout_data['shippingAddressFromData']['city_id'] = address_customer.city_id;
                    checkout_data['shippingAddressFromData']['city'] = address_customer.city;
                    checkout_data['shippingAddressFromData']['region_id'] = address_customer.region_id;
                    checkout_data['shippingAddressFromData']['region'] = address_customer.region;
                    checkout_data['shippingAddressFromData']['township_id'] = address_customer.township_id;
                    checkout_data['shippingAddressFromData']['township'] = address_customer.township;
                    checkout_data['shippingAddressFromData']['street'] = {0: address_customer.street};
                }
                
                localStorage.setItem("mage-cache-storage", JSON.stringify(mage_cache_storage));
            }
        }
        return this;
    };
    $(document).ready(function() {
        // call update current address for pricequote
        $(this).addressCheckoutAddressUpdate();

        $(document).on("click", '.quantity-minus', function() {
            var input = $(this).parent().find('input');
            var value = parseInt(input.val());
            var finalValue = value - 1;
            if (finalValue >= 1) {
                input.val(finalValue);
                input.trigger("change");
            }
        });
        $(document).on("click", '.quantity-plus', function() {
            var input = $(this).parent().find('input');
            var value = parseInt(input.val());
            var finalValue = value + 1;
            input.val(finalValue);
            input.trigger("change");
        });
        $(document).on("click", '.remove-item', function(evt) {
            var dataPost = $(this).data("post");
            if (dataPost["id"]) {
                var inputQtyId = "#cart-" + dataPost["id"] + "-qty";
                $(inputQtyId).val(0);
                $(inputQtyId).trigger("change");
            }
        });

        // when enter input qty = 0 => will set to old qty
        $(document).on('keypress', 'input[name$="[qty]"]', function(e) {
            var keycode = (e.keyCode ? e.keyCode : e.which);
            var currentQty = $(this).val();
            if(keycode == '13' && Number(currentQty) < 1){
                $(this).val($(this).attr('oldQty'));
                return false;
            }
        });

        $(document).on('change', 'input[name$="[qty]"]', function() {
            var dataPost = $(this).data("post");
            var currentQty = $(this).val(),
                defaultStockQty = $(this).attr('default-stock'),
                defaultStockPromoQty = $('#default-stock-promo-'+ dataPost).val();
            // var $flag = '';
            
            if(defaultStockPromoQty !== null && defaultStockPromoQty !== ''  && defaultStockPromoQty !== undefined) {
                var array = JSON.parse(defaultStockPromoQty);
                jQuery.each(array, function(index, item) {
                    var default_stock_promo = Number(item.default_stock_promo);
                    var sum_default_stock = Number(item.sum_default_stock);
                    var qtyProduct =  Number(item.qty_promo_current);

                    var defaultStock = default_stock_promo - (sum_default_stock + Number(currentQty) - qtyProduct);

                    if (Number(defaultStock) < 0) {
                        $.toaster({ title: $t('Notice'), priority: 'danger', message: 'Sản phẩm quà tặng '+item.name_product+ ' chỉ còn '+ item.default_stock_promo+' sản phẩm' });
                        return false;
                    }
                    
                    // if (Number(currentQty) > Number(item.default_stock_promo)) {
                    //     $.toaster({ title: $t('Notice'), priority: 'danger', message: 'Sản phẩm quà tặng '+item.name_product+ ' chỉ còn '+ item.default_stock_promo+' sản phẩm' });
                    //     $flag = 'error';
                    //     return false;
                    // }
                    // if (Number(item.default_stock_promo) <= 0) {
                    //     $.toaster({ title: $t('Notice'), priority: 'danger', message: 'Sản phẩm quà tặng '+item.name_product+ ' tạm hết hàng.' });
                    //     $flag = 'error';
                    //     return false;
                    // }
                });
            }
            var dataCartItemId = $(this).data("cart-item-id");
            var qtyTotalProductMain = 0;
            $(".input-text.qty").each(function() {
                var dataCartItemIdChil = $(this).data("cart-item-id");
                if(dataCartItemId === dataCartItemIdChil){
                    qtyTotalProductMain += Number($(this).val());
                }
            });
            $(this).attr('oldQty', currentQty);
            if (Number(qtyTotalProductMain) > Number(defaultStockQty)) {
                $(this).val($(this).attr('oldQty'));
                $.toaster({ title: $t('Notice'), priority: 'danger', message: $t('Chỉ còn ' + defaultStockQty + ' sản phẩm') });
                // $.toaster({ title: $t('Notice'), priority: 'danger', message: $t('Only %1 item left', defaultStockQty) });
            }

            var form = $('form#form-validate');
            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
                showLoader: true,
                success: function(res) {
                    var parsedResponse = $.parseHTML(res);
                    var contentId = "#price-quote-content";
                    var result = $(parsedResponse).find(contentId);
                    var sections = ['cart'];

                    if($(parsedResponse).find('.cart-empty').length > 0){
                        location.reload();
                        return false;
                    }
                    

                    $(contentId).replaceWith(result);

                    // The mini cart reloading
                    customerData.reload(sections, true);

                    // Lazy Image
                    if (typeof $("img[data-amsrc]").lazy === 'function') {
                        $("img[data-amsrc]").lazy({ "attribute": "data-amsrc" });
                    }
                },
                error: function(xhr, status, error) {
                    // var err = eval(xhr.responseText);
                    // console.log(xhr);
                    // console.log(status);
                    console.log(error);
                    console.log(xhr.responseText);
                }
            });
        });

        // submit apply address
        $(document).on('click', '#shipping_address_cart', function(e) {
            e.preventDefault();
            $("input[name='type_select_address']").val("button");
            sendAjaxShippingAddress();
        });

        $(document).on('click', '#shipping_address_cart_option', function(e) {
            e.preventDefault();
            $("input[name='type_select_address']").val("option");
            $(".shipping-address #region_id").addClass("default-border");
            $(".shipping-address #city_id").addClass("default-border");
            $(".shipping-address #city").addClass("default-border");
            $(".shipping-address #township_id").addClass("default-border");
            $(".shipping-address #township").addClass("default-border");
            sendAjaxShippingAddress();
        });

        function sendAjaxShippingAddress() {
            var form = $('form#form-validate');
            var status = form.validation('isValid'); //validates form and returns boolean
            var submit_validate = false;
            var type_select_address = $("input[name='type_select_address']").val();

            // check submit validate
            if (type_select_address == 'option') {
                submit_validate = true;
            } else if (status && type_select_address == 'button') {
                submit_validate = true;
            }

            if (submit_validate) {
                $.ajax({
                    url: form.attr('action'),
                    data: form.serialize(),
                    showLoader: true,
                    beforeSend: function() {
                        $('.loading-mask').css("display", "block");
                        var input_type_select_address = $("input[name='type_select_address']").val();

                        switch(input_type_select_address) {
                            case 'button':
                                // trigger select option region
                                $('#shipping-zip-form select[name="region_id"]').val($('.shipping-address select[name="region_id"]').val());
                                $('#shipping-zip-form select[name="region_id"]').children('option').each(function() {
                                    if ($(this).is(':selected'))
                                    { $(this).trigger('change');  }
                                });
                                // trigger select option region
                                $('#shipping-zip-form select[name="custom_attributes[city_id]"]').val($('.shipping-address select[name="city_id"]').val());
                                $('#shipping-zip-form select[name="custom_attributes[city_id]"]').children('option').each(function() {
                                    if ($(this).is(':selected'))
                                    { $(this).trigger('change');  }
                                });
                                $('#shipping-zip-form select[name="city_id"]').val($('.shipping-address select[name="city_id"]').val());
                                $('#shipping-zip-form select[name="city_id"]').children('option').each(function() {
                                    if ($(this).is(':selected'))
                                    { $(this).trigger('change');  }
                                });
                                $('#shipping-zip-form select[name="custom_attributes[township_id]"]').val($('.shipping-address select[name="township_id"]').val());
                                $('#shipping-zip-form select[name="custom_attributes[township_id]"]').children('option').each(function() {
                                    if ($(this).is(':selected'))
                                    { $(this).trigger('change');  }
                                });
                                $('#shipping-zip-form select[name="township_id"]').val($('.shipping-address select[name="township_id"]').val());
                                $('#shipping-zip-form select[name="township_id"]').children('option').each(function() {
                                    if ($(this).is(':selected'))
                                    { $(this).trigger('change');  }
                                });
                                $('#shipping-zip-form input[name="street[0]"]').val($('.shipping-address input[name="street"]').val());
                                $('#shipping-zip-form input[name="street[0]"]').trigger("change");
                                
                            break;

                            case 'option':
                                var address_customer = JSON.parse($('input[name="address-customer"]:checked').val());
                                $('#shipping-zip-form select[name="region_id"]').val(address_customer.region_id);
                                $('#shipping-zip-form select[name="region_id"]').children('option').each(function() {
                                    if ($(this).is(':selected'))
                                    { $(this).trigger('change');  }
                                });
                                // trigger select option region
                                $('#shipping-zip-form select[name="custom_attributes[city_id]"]').val(address_customer.city_id);
                                $('#shipping-zip-form select[name="custom_attributes[city_id]"]').children('option').each(function() {
                                    if ($(this).is(':selected'))
                                    { $(this).trigger('change');  }
                                });
                                $('#shipping-zip-form select[name="city_id"]').val(address_customer.city_id);
                                $('#shipping-zip-form select[name="city_id"]').children('option').each(function() {
                                    if ($(this).is(':selected'))
                                    { $(this).trigger('change');  }
                                });
                                $('#shipping-zip-form select[name="custom_attributes[township_id]"]').val(address_customer.township_id);
                                $('#shipping-zip-form select[name="custom_attributes[township_id]"]').children('option').each(function() {
                                    if ($(this).is(':selected'))
                                    { $(this).trigger('change');  }
                                });
                                $('#shipping-zip-form select[name="township_id"]').val(address_customer.township_id);
                                $('#shipping-zip-form select[name="township_id"]').children('option').each(function() {
                                    if ($(this).is(':selected'))
                                    { $(this).trigger('change');  }
                                });
                                $('#shipping-zip-form input[name="street[0]"]').val(address_customer.street);
                                $('#shipping-zip-form input[name="street[0]"]').trigger("change");

                                // var mage_cache_storage = JSON.parse(localStorage.getItem("mage-cache-storage"));
                                // if(mage_cache_storage !== null){
                                //     if(typeof mage_cache_storage['checkout-data'] !== 'undefined'){
                                //         var checkout_data = mage_cache_storage['checkout-data'];
                                //         if(typeof address_customer.entity_id != 'undefined'){
                                //             // set select option checkout
                                //             checkout_data['selectedShippingAddress'] = 'customer-address'+address_customer.entity_id;
                                //         }else{
                                //             // set select option checkout with new address
                                //             checkout_data['selectedShippingAddress'] = "new-customer-address";
                                //             checkout_data['newCustomerShippingAddress']['save_in_address_book'] = 1;
                                //             checkout_data['newCustomerShippingAddress']['city_id'] = address_customer.city_id;
                                //             checkout_data['newCustomerShippingAddress']['region_id'] = address_customer.region_id;
                                //             checkout_data['newCustomerShippingAddress']['township_id'] = address_customer.township_id;
                                //             checkout_data['newCustomerShippingAddress']['street'][0] = address_customer.street;
                                //         }
                                        
                                //         localStorage.setItem("mage-cache-storage", JSON.stringify(mage_cache_storage));
                                //     }
                                // }

                            break;
                        }
                    },
                    success: function(res) {
                        var parsedResponse = $.parseHTML(res);
                        var contentId = "#price-quote-content";
                        var result = $(parsedResponse).find(contentId);
                        var sections = ['cart'];

                        $(contentId).replaceWith(result);

                        // The mini cart reloading
                        customerData.reload(sections, true);

                        // call update current address for pricequote
                        $(this).addressCheckoutAddressUpdate();

                        // Lazy Image
                        // if (typeof $("img[data-amsrc]").lazy === 'function'){
                        //     $("img[data-amsrc]").lazy({"attribute": "data-amsrc"});
                        // }  
                    },
                    error: function(xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    }
                });
            } else {
                $.toaster({ title: $t('Notice'), priority: 'danger', message: $t('This is the required case') });
            }
        }

        $(document).on('click', '.price-quote-shipping-address', function(e) {
            if($('.shipping-address:hidden').length){
                $('.shipping-address').css("display", "block");
            }else{
                $('.shipping-address').css("display", "none");
            }
        });
    });
});
