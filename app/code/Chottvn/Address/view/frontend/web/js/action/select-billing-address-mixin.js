define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';
    return function (selectBillingAddressAction) {
        return wrapper.wrap(selectBillingAddressAction, function (originalAction, billingAddress) {
            if(billingAddress != undefined) {
                if (billingAddress['extension_attributes'] === undefined) {
                    billingAddress['extension_attributes'] = {};
                }
                if (billingAddress.customAttributes != undefined) {
                    $.each(billingAddress.customAttributes, function(key, item) {
                        if (typeof item == 'object') {
                            billingAddress[item.attribute_code] = item.value;
                        } else {
                            billingAddress[key] = item;
                        }
                    });
                }
                if(billingAddress.city_id != undefined) {
                    billingAddress['extension_attributes']['city_id'] = billingAddress.city_id;
                    delete billingAddress.city_id;
                }
                if(billingAddress.township != undefined) {
                    billingAddress['extension_attributes']['township'] = billingAddress.township;
                }
                if(billingAddress.township_id != undefined) {
                    billingAddress['extension_attributes']['township_id'] = billingAddress.township_id;
                    delete billingAddress.township_id;
                }

                if (billingAddress['extension_attributes'] != undefined && billingAddress['extension_attributes']['township'] != undefined) {
                    billingAddress.township = billingAddress['extension_attributes']['township'];
                }
            }

            return originalAction(billingAddress);
        });
    };
});