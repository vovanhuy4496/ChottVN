/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/place-order'
], function($, quote, urlBuilder, customer, placeOrderService) {
    'use strict';

    return function(paymentData, messageContainer) {
        var serviceUrl, payload;

        var billingAddress = quote.billingAddress();
        var shippingAddress = quote.shippingAddress();

        if (billingAddress != undefined) {
            if (billingAddress['extension_attributes'] === undefined) {
                billingAddress['extension_attributes'] = {};
            }
            if (billingAddress.customAttributes != undefined) {
                jQuery.each(billingAddress.customAttributes, function(key, item) {
                    billingAddress[item.attribute_code] = item.value;
                });
            }
            if (billingAddress.city_id != undefined) {
                billingAddress['extension_attributes']['city_id'] = billingAddress.city_id;
                delete billingAddress.city_id;
            }
            if (billingAddress.township != undefined) {
                billingAddress['extension_attributes']['township'] = billingAddress.township;
                delete billingAddress.township;
            }
            if (billingAddress.township_id != undefined) {
                billingAddress['extension_attributes']['township_id'] = billingAddress.township_id;
                delete billingAddress.township_id;
            }
            // if checked 'Người khác nhận hàng'
            var checked = $('input[name="others_receive_products"]').is(':checked');
            // var isLoggedIn = ko.observable(window.isCustomerLoggedIn);

            // if (isLoggedIn()) {
            //     customerData = window.customerData;

            //     var telephone = '';
            //     var firstname = customerData.firstname;
            //     var email = customerData.email;

            //     if (typeof customerData != 'undefined' && typeof customerData.custom_attributes != 'undefined' && typeof customerData.custom_attributes.phone_number != 'undefined') {
            //         telephone = customerData.custom_attributes.phone_number.value;
            //     }

            //     // neu co login, billingAddress = customer info
            //     billingAddress.email = email;
            //     billingAddress.firstname = firstname;
            //     billingAddress.telephone = telephone;
            // } else { // neu ko co login, billingAddress = field input
            //     billingAddress.email = $('input[name="email_ctt"]').val();
            //     billingAddress.firstname = $('input[name="firstname_ctt"]').val();
            //     billingAddress.telephone = $('input[name="telephone_ctt"]').val();
            // }

            // var email = billingAddress.email ? billingAddress.email : '';

            // billingAddress['extension_attributes']['email'] = email;
            // shippingAddress['extension_attributes']['email'] = email;

            billingAddress['extension_attributes']['fee_shipping_contact'] = $("input[name='fee_shipping_contact']").val();
            billingAddress['extension_attributes']['affiliate_account_code'] = $("input[name='affiliate_account_code']").val();
            // neu co check nguoi khac nhan hang
            if (checked) {
                shippingAddress.email = $('input[name="others_email"]').val();
                shippingAddress.firstname = $('input[name="others_fullname"]').val();
                shippingAddress.telephone = $('input[name="others_telephone"]').val();

                billingAddress['extension_attributes']['others_receive_products'] = $('input[name="others_receive_products"]').is(':checked');
                billingAddress['extension_attributes']['others_email'] = $('input[name="others_email"]').val();
                billingAddress['extension_attributes']['others_fullname'] = $('input[name="others_fullname"]').val();
                billingAddress['extension_attributes']['others_telephone'] = $("input[name='others_telephone']").val();
            } else {
                shippingAddress.email = $('input[name="email_ctt"]').val();
                shippingAddress.firstname = $('input[name="firstname_ctt"]').val();
                shippingAddress.telephone = $('input[name="telephone_ctt"]').val();
            }

            billingAddress['extension_attributes']['email_ctt'] = $('input[name="email_ctt"]').val();
            billingAddress['extension_attributes']['firstname_ctt'] = $('input[name="firstname_ctt"]').val();
            billingAddress['extension_attributes']['telephone_ctt'] = $('input[name="telephone_ctt"]').val();
        }

        payload = {
            cartId: quote.getQuoteId(),
            billingAddress: billingAddress,
            shippingAddress: shippingAddress,
            // billingAddress: quote.billingAddress(),
            paymentMethod: paymentData
        };

        if (customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
        } else {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/payment-information', {
                quoteId: quote.getQuoteId()
            });
            payload.email = quote.guestEmail;
        }

        return placeOrderService(serviceUrl, payload, messageContainer);
    };
});