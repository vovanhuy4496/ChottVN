define(
    [
        'jquery',
        'ko',
        'mage/translate',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/quote',
        'mageUtils',
        'jquery/ui',
        'jquery/validate',
    ],
    function($, ko, $t, messageList, quote, utils) {
        'use strict';
        return {
            validate: function() {
                // console.log(quote.shippingAddress());
                var isValid = true,
                    isLoggedIn = ko.observable(window.isCustomerLoggedIn),
                    // customerData = {},
                    region = quote.shippingAddress().region,
                    regionId = quote.shippingAddress().regionId,
                    city = quote.shippingAddress().city,
                    city_id = quote.shippingAddress().extension_attributes.city_id,
                    township = quote.shippingAddress().extension_attributes.township,
                    township_id = quote.shippingAddress().extension_attributes.township_id,
                    street = quote.shippingAddress().street;

                var region_ = $('input[name="region"]').val(),
                    regionId_ = $('select[name="region_id"]').val(),
                    city_ = $('input[name="city"]').val(),
                    cityId_ = $('select[name="custom_attributes[city_id]"]').val(),
                    township_ = $('input[name="custom_attributes[township]"]').val(),
                    townshipId_ = $('select[name="custom_attributes[township_id]"]').val(),
                    street_ = $('input[name="street[0]"]').val();
                if (isLoggedIn()) {
                    cityId_ = $('select[name="city_id"]').val();
                    township_ = $('input[name="township"]').val();
                    townshipId_ = $('select[name="township_id"]').val();
                }
                // console.log(city);
                // console.log(cityId_);
                $('input[name="city"]').val('');
                if (!utils.isEmpty(city) || cityId_) {
                    var getSelectCity = isLoggedIn() ? $('select[name="city_id"] :selected').text() : $('select[name="custom_attributes[city_id]"] :selected').text();
                    $('input[name="city"]').val(city ? city : getSelectCity);
                    // console.log($('input[name="city"]').val());
                }
                if (utils.isEmpty(region) && utils.isEmpty(regionId)) {
                    isValid = false;
                }
                if (utils.isEmpty(city) && utils.isEmpty(city_id)) {
                    isValid = false;
                }
                if (utils.isEmpty(township) && utils.isEmpty(township_id)) {
                    isValid = false;
                }
                if (street.length == 0) {
                    isValid = false;
                }
                if (utils.isEmpty(region_) && utils.isEmpty(regionId_)) {
                    // $('input[name="region"]').css('border-color', '#ff6000');
                    $('select[name="region_id"]').css('border-color', '#ff6000');
                    $('select[name="region_id"]').focus();
                }
                if (utils.isEmpty(city_) && utils.isEmpty(cityId_)) {
                    $('input[name="city"]').css('border-color', '#ff6000');
                    $('select[name="custom_attributes[city_id]"]').css('border-color', '#ff6000');
                    $('select[name="city_id"]').css('border-color', '#ff6000');
                    $('select[name="custom_attributes[city_id]"]').focus();
                }
                if (utils.isEmpty(township_)) {
                    $('input[name="custom_attributes[township]"]').css('border-color', '#ff6000');
                    $('input[name="township"]').css('border-color', '#ff6000');
                    $('input[name="custom_attributes[township]"]').focus();
                }
                if (utils.isEmpty(townshipId_)) {
                    $('select[name="custom_attributes[township_id]"]').css('border-color', '#ff6000');
                    $('select[name="township_id"]').css('border-color', '#ff6000');
                    $('select[name="custom_attributes[township_id]"]').focus();
                }
                // console.log(street_);
                if (utils.isEmpty(street_)) {
                    $('input[name="street[0]"]').css('border-color', '#ff6000');
                    $('input[name="street[0]"]').focus();
                }
                // if (isLoggedIn() && !isValid) {
                //     if ($('.action.edit-address-link-ctt').hasClass('can-edit-address-link')) {
                //         $(".shipping-address-items").find('.action.edit-address-link-ctt').trigger('click');
                //     }
                // }
                // console.log(utils.isEmpty(region));
                // console.log(utils.isEmpty(regionId));
                // console.log(utils.isEmpty(city));
                // console.log(utils.isEmpty(city_id));
                // console.log(utils.isEmpty(township_id));
                // console.log(street);
                // console.log(quote.shippingAddress());
                if (!isValid) {
                    $.toaster({ title: $t('Street Address'), priority: 'danger', message: $t('Please complete') + ' ' + $t('Street Address') });
                }

                return isValid;
            }
        }
    }
);