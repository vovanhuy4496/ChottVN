define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            var billingAddress = quote.billingAddress();
            if(billingAddress != undefined) {
                if (billingAddress['extension_attributes'] === undefined) {
                    billingAddress['extension_attributes'] = {};
                }
                if (billingAddress.customAttributes != undefined) {
                    $.each(billingAddress.customAttributes, function(key, item) {
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

            return originalAction(paymentData, messageContainer);
        });
    };
});
