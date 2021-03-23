/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
        'use strict';

        return function (placeOrderAction) {
            return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
                /* added email optional*/
                if(quote.guestEmail == null) {
                    var orderEmail = $("input[name='guest_email']").val();
                    if (orderEmail){
                        quote.guestEmail = orderEmail;
                    }else{
                        var hostName = window.location.hostname;
                        quote.guestEmail =  'guest_'+Date.now()+'@'+hostName;
                    }                    
                }
                $.fn.changeVal = function (v) {
                    return this.val(v).trigger("change");
                }
                $("input[name='login[username]']").changeVal();

                return originalAction(paymentData, messageContainer);
            });
        };
    }
);