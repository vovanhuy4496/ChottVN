/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/place-order'
    ],
    function ($, quote, urlBuilder, customer, placeOrderService) {
        'use strict';
 
        return function (paymentData, messageContainer) {
            var serviceUrl, payload;            
            
            var billingAddress = quote.billingAddress(); 
            if(billingAddress != undefined) {
                if (billingAddress['extension_attributes'] === undefined) {
                    billingAddress['extension_attributes'] = {};
                }
                if (billingAddress.customAttributes != undefined) {
                    jQuery.each(billingAddress.customAttributes, function(key, item) {
                        billingAddress[item.attribute_code] = item.value;
                    });
                }
                if(billingAddress.city_id != undefined) {
                    billingAddress['extension_attributes']['city_id'] = billingAddress.city_id;
                    delete billingAddress.city_id;
                }
                if(billingAddress.township != undefined) {
                    billingAddress['extension_attributes']['township'] = billingAddress.township;
                    delete billingAddress.township;
                }
                if(billingAddress.township_id != undefined) {
                    billingAddress['extension_attributes']['township_id'] = billingAddress.township_id;
                    delete billingAddress.township_id;
                }
            }
            payload = {
                cartId: quote.getQuoteId(),
                //billingAddress: quote.billingAddress(),
                billingAddress: billingAddress,
                paymentMethod: paymentData
            };

            if (customer.isLoggedIn()) {                    
                serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
            } else {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/payment-information', {
                    quoteId: quote.getQuoteId()
                });
                
                /* added email optional*/
                if(quote.guestEmail == null) {
                    quote.guestEmail =  'guest_'+Date.now()+'@chott.vn';
                }
                payload.email = quote.guestEmail;
            }
 
            return placeOrderService(serviceUrl, payload, messageContainer);
        };
    }
);