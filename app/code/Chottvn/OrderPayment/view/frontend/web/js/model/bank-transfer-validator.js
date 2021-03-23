define(
    [
    'jquery',
    'mage/translate', 
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/quote'],
    function (jQuery, $t, messageList, quote) {
        'use strict';
        return {
            validate: function () {
                var isValid = true; 
                var paymentMethod = quote.paymentMethod();
                if (paymentMethod && paymentMethod.method == "banktransfer"){
                    var fieldBankTransferNote = jQuery("textarea[name='bank_transfer_note']");                    
                    var bankTransferNoteVal = fieldBankTransferNote.text();
                    
                    if (bankTransferNoteVal == undefined || bankTransferNoteVal.length == 0 ) {
                        isValid = false;
                    }
                }
                if (!isValid) {
                    messageList.addErrorMessage({ message: $t('Please select Bank Transfer Account') });
                }
                return isValid;
            }
        }
    }
);