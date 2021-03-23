define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Chottvn_OrderPayment/js/model/others-fullname-validator'
    ],
    function(Component, additionalValidators, validate) {
        'use strict';
        additionalValidators.registerValidator(validate);
        return Component.extend({});
    }
);