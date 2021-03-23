define([
    'jquery',
    'ko',
    'mage/utils/wrapper',
    'Amasty_Orderattr/js/model/attribute-sets/payment-attributes',
    'Amasty_Orderattr/js/model/validate-and-save',
    'Magento_Checkout/js/model/quote',
], function($, ko, wrapper, attributesForm, validateAndSave, quote) {
    'use strict';

    return function(placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function(originalAction, paymentData, messageContainer) {
            var result = $.Deferred(),
                isLoggedIn = ko.observable(window.isCustomerLoggedIn);

            $.cookie("region_id", null);
            $.cookie("checkoutBankId", null);
            $.cookie("checkOutofStock", null);
            // set value Yêu cầu xuất hóa đơn
            var checked_vat_ctt = $('input[name="vat_invoice_required_ctt"]').is(':checked');
            if (checked_vat_ctt) {
                $('input[name="vat_company"]').val($('input[name="vat_company_ctt"]').val()).trigger("change");
                $('input[name="vat_address"]').val($('input[name="vat_address_ctt"]').val()).trigger("change");
                $('input[name="vat_number"]').val($('input[name="vat_number_ctt"]').val()).trigger("change");
                $('input[name="vat_contact_name"]').val($('input[name="vat_contact_name_ctt"]').val()).trigger("change");
                $('input[name="vat_contact_phone_number"]').val($('input[name="vat_contact_phone_number_ctt"]').val()).trigger("change");
                $('input[name="vat_contact_email"]').val($('input[name="vat_contact_email_ctt"]').val()).trigger("change");
            }

            // $('input[name="region"]').val($('input[name="region"]').val()).trigger("change");
            // $('select[name="region_id"]').val($('select[name="region_id"]').val()).trigger("change");
            // $('input[name="city"]').val($('input[name="city"]').val()).trigger("change");

            // if (isLoggedIn() && $('.action.edit-address-link-ctt').hasClass('can-edit-address-link')) {
            //     // $('input[name="city"]').val($('input[name="city"]').val()).trigger("change");
            //     $('select[name="city_id"]').val($('select[name="city_id"]').val()).trigger("change");

            //     $('input[name="township"]').val($('input[name="township"]').val()).trigger("change");
            //     $('select[name="township_id"]').val($('select[name="township_id"]').val()).trigger("change");
            // } else {
            //     // $('input[name="city"]').val($('input[name="city"]').val()).trigger("change");
            //     $('select[name="custom_attributes[city_id]"]').val($('select[name="custom_attributes[city_id]"]').val()).trigger("change");

            //     $('input[name="custom_attributes[township]"]').val($('input[name="custom_attributes[township]"]').val()).trigger("change");
            //     $('select[name="custom_attributes[township_id]"]').val($('select[name="custom_attributes[township_id]"]').val()).trigger("change");
            // }
            // $('input[name="telephone"]').val($('input[name="telephone_ctt"]').val()).trigger("change");
            // $('input[name="street[0]"]').val($('input[name="street[0]"]').val()).trigger("change");
            // console.log(quote.shippingAddress());
            // console.log($('input[name="city"]').val());

            // if (quote.shippingAddress().city) {
            //     quote.shippingAddress().city = '';
            //     quote.shippingAddress().city = $('input[name="city"]').val();
            // }
            // if (!isLoggedIn()) {
            //     // console.log();
            //     if ($('input[name="city"]').val() == '') {
            //         $('input[name="city"]').val($('.billing-address-form select[name="custom_attributes[city_id]"] :selected').text()).trigger("change");
            //     }
            //     quote.shippingAddress().city = $('.billing-address-form select[name="custom_attributes[city_id]"] :selected').text();
            //     // quote.shippingAddress().city_id = $('select[name="custom_attributes[city_id]"]').val();
            //     // quote.shippingAddress().township = $('input[name="township"]').val();
            //     // quote.shippingAddress().township_id = $('select[name="township_id"]').val();
            //     // quote.shippingAddress().street[0] = $('input[name="street[0]"]').val();
            // }
            // console.log(quote);

            validateAndSave(attributesForm).done(
                function() {
                    $.when(
                        originalAction(paymentData, messageContainer)
                    ).fail(
                        function() {
                            result.reject.apply(this, arguments);
                        }
                    ).done(
                        function() {
                            result.resolve.apply(this, arguments);
                        }
                    );
                }
            ).fail(
                function() {
                    result.reject();
                }
            );

            return result.promise();
        });
    };
});