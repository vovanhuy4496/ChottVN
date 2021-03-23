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
            var result = $.Deferred();

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