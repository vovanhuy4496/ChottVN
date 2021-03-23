define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';
    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {
            var customAttributes = {};
            if (messageContainer.city_id !== undefined &&
                typeof messageContainer.city_id != 'object' &&
                messageContainer.city_id !== ''
            ) {
                customAttributes['city_id'] = {'attribute_code':'city_id', 'value': messageContainer.city_id};
            }
            if (messageContainer.township !== undefined &&
                typeof messageContainer.township != 'object' &&
                messageContainer.township !== ''
            ) {
                customAttributes['township'] = {'attribute_code':'township', 'value': messageContainer.township};
            }
            if (messageContainer.township_id !== undefined &&
                typeof messageContainer.township_id != 'object' &&
                messageContainer.township_id !== ''
            ) {
                customAttributes['township_id'] = {'attribute_code':'township_id', 'value': messageContainer.township_id};
            }
            messageContainer['customAttributes'] = customAttributes;

            return originalAction(messageContainer);
        });
    };
});