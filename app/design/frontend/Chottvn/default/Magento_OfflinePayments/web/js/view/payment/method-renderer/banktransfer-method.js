/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([
    'ko',
    'jquery',
    'Magento_Checkout/js/view/payment/default'
], function(ko, $, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_OfflinePayments/payment/banktransfer'
        },

        /**
         * Get value of instruction field.
         * @returns {String}
         */
        getInstructions: function() {
            //return window.checkoutConfig.payment.instructions[this.item.method];            
            var data = window.checkoutConfig.payment.instructions[this.item.method];
            var decodeData = _.unescape(data);
            return decodeData;
        },

        /**
         * Get value of instruction field.
         * @returns {String}
         */
        getBankAccountInstructions: function() {
            //return window.checkoutConfig.payment.instructions[this.item.method];            
            var data = window.bankAccountInstructions;
            var decodeData = _.unescape(data);
            var radio = "id='radio_" + $.cookie('checkoutBankId') + "'";
            if ($.cookie("checkoutBankId")) {
                decodeData = decodeData.replace("checked='true'", "");
                decodeData = decodeData.replace(radio, radio + " " + "checked='true'");
            }

            return decodeData;
        }
    });
});