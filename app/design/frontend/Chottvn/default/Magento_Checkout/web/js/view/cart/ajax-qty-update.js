define([
    'jquery',
    'ko',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Customer/js/customer-data',
    'mage/translate',
], function($, ko, getTotalsAction, customerData, $t) {

    $(document).ready(function() {
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
        $(document).on('keypress', 'input[name$="[qty]"]', function(e) {
            if (e.which == 13) {
                if ($(this).attr('oldQty') != $(this).val()) {
                    e.preventDefault();
                    var input = $(e.target);
                    var value = parseInt(input.val());
                    input.val(value);
                    input.trigger("change");
                }
                return false;
            }
            return true;
        });
        $(document).on('change', 'input[name$="[qty]"]', function() {
            var dataPost = $(this).data("post");
            var currentQty = $(this).val(),
                defaultStockQty = $(this).attr('default-stock'),
                defaultStockPromoQty = $('#default-stock-promo-'+ dataPost).val();

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
                    var formData = $(parsedResponse).find("#form-validate");
                    var pageTitle = $(parsedResponse).find(".page-title");
                    var sections = ['cart'];

                    $("#form-validate").replaceWith(formData);
                    $(".page-title").replaceWith(pageTitle);

                    // The mini cart reloading
                    customerData.reload(sections, true);

                    // Lazy Image
                    // if (typeof $("img[data-amsrc]").lazy === 'function'){
                    //     $("img[data-amsrc]").lazy({"attribute": "data-amsrc"});
                    // }      

                    // The totals summary block reloading
                    var deferred = $.Deferred();
                    getTotalsAction([], deferred);
                },
                error: function(xhr, status, error) {
                    // var err = eval("(" + xhr.responseText + ")");
                    // console.log(err.Message);
                    console.log(error);
                    console.log(xhr.responseText);
                }
            });
        });
        $('.form-control').on("keyup", function() {
            $('.form-control').css('border-color', '#e0e0e0');
        });

        function validateTextbox(value) {
            return value.length > 0;
        }
        // submit apply address
        $(document).on('click', '#shipping_address_cart', function(e) {
            e.preventDefault();
            var isValid = true;
            var region_id = $('select[name="region_id"]').val();
            var region = $('input[name="region"]').val();
            var township_id = $('select[name="township_id"]').val();
            var township = $('input[name="township"]').val();
            var city_id = $('select[name="city_id"]').val();
            var city = $('input[name="city"]').val();
            var street = $('input[name="street"]').val();
            $(".form-group-required").find('select').each(function(index, element) {
                if ($(this).val().trim() == '') {
                    isValid = false;
                    $("input[name='city']").hide();
                    $("#city_id").show();
                    $("input[name='township']").hide();
                    $("#township_id").show();
                    $(this).attr('style', 'border-color:#ff6000 !important;');
                    $.toaster({ title: $t('Notice'), priority: 'danger', message: $t('This is the required case') });
                } else {
                    $(this).attr('style', 'border-color:#f5f5f5 !important;');
                }
            })
            if (isValid) {
                $("input[name='type_select_address']").val("button");
                // mapping
                $('select[name="region_id"]').val(region_id).trigger("change");
                $('input[name="region"]').val(region).trigger("change");
                $('input[name="city"]').val(city).trigger("change");
                var isLoggedIn = ko.observable(window.isCustomerLoggedIn);
                if (isLoggedIn()) {
                    $('select[name="city_id"]').val(city_id).trigger("change");
                    $('select[name="township_id"]').val(township_id).trigger("change");
                    $('input[name="township"]').val(township).trigger("change");
                } else {
                    $('select[name="custom_attributes[city_id]"]').val(city_id).trigger("change");
                    $('select[name="custom_attributes[township_id]"]').val(township_id).trigger("change");
                    $('input[name="custom_attributes[township]"]').val(township).trigger("change");
                }

                $('input[name="street[0]"]').val(street).trigger("change");
                sendAjaxShippingAddress();
            }
        });

        $(document).on('click', '#shipping_address_cart_option', function(e) {
            e.preventDefault();
            $("input[name='type_select_address']").val("option");
            $(".shipping-address #region_id").addClass("default-border");
            $(".shipping-address #city_id").addClass("default-border");
            $(".shipping-address #city").addClass("default-border");
            $(".shipping-address #township_id").addClass("default-border");
            $(".shipping-address #township").addClass("default-border");
            if ($('input[name="address-customer"]:checked')) {
                $('input[name="address-customer"]:checked').checked = true;
            }
            // mapping
            $array_address = JSON.parse($('input[name="address-customer"]:checked').val());
            $region_id = $array_address.region_id;
            $city_id = $array_address.city_id;
            $township_id = $array_address.township_id;
            $township = $array_address.township;
            $street = $array_address.street;
            $('select[name="region_id"]').val($region_id).trigger("change");
            $('select[name="city_id"]').val($city_id).trigger("change");
            $('select[name="township_id"]').val($township_id).trigger("change");
            $('input[name="township"]').val($township).trigger("change");
            $('input[name="street[0]"]').val($street).trigger("change");
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
                    success: function(res) {
                        var parsedResponse = $.parseHTML(res);
                        var contentId = "#cart-content";
                        var result = $(parsedResponse).find(contentId);
                        var sections = ['cart'];

                        $(contentId).replaceWith(result);

                        // The mini cart reloading
                        customerData.reload(sections, true);

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
            if ($('.shipping-address:hidden').length) {
                $('.shipping-address').css("display", "block");
            } else {
                $('.shipping-address').css("display", "none");
            }
        });
    });
});