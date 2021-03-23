define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper,quote) {
    'use strict';
    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }
            if (shippingAddress.customAttributes != undefined) {
                $.each(shippingAddress.customAttributes, function(key, item) {
                    if (typeof item == 'object') {
                        shippingAddress[item.attribute_code] = item.value;
                    } else {
                        shippingAddress[key] = item;
                    }
                });
            }
            if(shippingAddress.city_id != undefined && shippingAddress.city_id != '') {
                shippingAddress['extension_attributes']['city_id'] = shippingAddress.city_id;
            }
            if(shippingAddress.township != undefined && shippingAddress.township != '') {
                shippingAddress['extension_attributes']['township'] = shippingAddress.township;
            }
            if(shippingAddress.township_id != undefined && shippingAddress.township_id != '') {
                shippingAddress['extension_attributes']['township_id'] = shippingAddress.township_id;
            }

            delete shippingAddress.city_id;
            delete shippingAddress.township;
            delete shippingAddress.township_id;

            var billingAddress = quote.billingAddress();
            if(billingAddress != undefined) {
                if (billingAddress['extension_attributes'] === undefined) {
                    billingAddress['extension_attributes'] = {};
                }
                if (billingAddress.customAttributes != undefined) {
                    $.each(billingAddress.customAttributes , function(key, item) {
                        if (typeof item == 'object') {
                            billingAddress[item.attribute_code] = item.value;
                        } else {
                            billingAddress[key] = item;
                        }
                    });
                }
                if(billingAddress.city_id != undefined && billingAddress.city_id != '') {
                    billingAddress['extension_attributes']['city_id'] = billingAddress.city_id;
                }
                if(billingAddress.township != undefined && billingAddress.township != '') {
                    billingAddress['extension_attributes']['township'] = billingAddress.township;
                }
                if(billingAddress.township_id != undefined && billingAddress.township_id != '') {
                    billingAddress['extension_attributes']['township_id'] = billingAddress.township_id;
                }

                delete billingAddress.city_id;
                delete billingAddress.township;
                delete billingAddress.township_id;
            }
            return originalAction(messageContainer);
        });
    };
});