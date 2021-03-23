define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Chottvn_OrderPayment/js/model/bank-transfer-validator'
    ],
    function (Component, additionalValidators, bankTransferValidator) {
        'use strict';
        additionalValidators.registerValidator(bankTransferValidator);
        return Component.extend({});
    }
);