define(
    [
        'jquery',
        'ko',
        'mage/translate',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/quote',
        'jquery/ui',
        'jquery/validate',
    ],
    function($, ko, $t, messageList, quote) {
        'use strict';
        return {
            validate: function() {
                var isValid = true;
                var paymentMethod = quote.paymentMethod();
                if (paymentMethod && paymentMethod.method == "banktransfer") {
                    var fieldBankTransferNote = $("textarea[name='bank_transfer_note']").val();
                    // console.log(fieldBankTransferNote);
                    if (fieldBankTransferNote == undefined || fieldBankTransferNote.length == 0) {
                        isValid = false;
                    }
                    // var bankTransferNoteVal = '';
                    // if ($.cookie("checkoutBankId")) {
                    //     var bankId = $.cookie("checkoutBankId");
                    //     bankTransferNoteVal = window.bankAccountDict[bankId]["instruction"];
                    // }
                    // if (bankTransferNoteVal == undefined || bankTransferNoteVal.length == 0) {
                    //     isValid = false;
                    // } else {
                    //     fieldBankTransferNote.text(bankTransferNoteVal).trigger('change');
                    // }
                }
                if (!isValid) {
                    $.toaster({ title: $t('Payment Method'), priority: 'danger', message: $t('Please select Bank Transfer Account') });
                }
                return isValid;
            }
        }
    }
);