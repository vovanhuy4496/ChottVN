define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Chottvn_OrderPayment/js/model/customer-info'
    ],
    function(Component, additionalValidators, validate) {
        'use strict';
        additionalValidators.registerValidator(validate);
        return Component.extend({});
    }
);