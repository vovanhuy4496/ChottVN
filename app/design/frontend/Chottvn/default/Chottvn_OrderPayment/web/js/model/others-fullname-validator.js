define(
    [
        'jquery',
        'mage/translate',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/quote',
        'jquery/ui',
        'jquery/validate',
    ],
    function($, $t, messageList, quote) {
        'use strict';
        return {
            validate: function() {
                var isValid = true,
                    check_receive = $('input[name="others_receive_products"]').is(':checked'),
                    check_show_error = $('div[name="shippingAddress.others_fullname"] .control div').hasClass('field-error'),
                    check_show_error_ctt = $('div[name="shippingAddress.others_fullname"] .control div').hasClass('field-error-ctt'),
                    check_exist_list_address = $('.amcheckout-wrapper .field').hasClass('addresses');

                if (check_receive) {
                    // if (check_exist_list_address && check_receive) {
                    if (!check_show_error && !check_show_error_ctt) {
                        if ($('input[name="others_fullname"]').val().trim() == '') { // zero-length string AFTER a trim
                            isValid = false;
                            var showError = '<div class="field-error-ctt"><span>Đây là trường bắt buộc.</span></div>';
                            $('div[name="shippingAddress.others_fullname"] .control').append(showError);
                            $('input[name="others_fullname"]').focus();
                            $('input[name="others_fullname"]').css('border-color', '#ed8380');
                        }
                    } else {
                        isValid = false;
                        if (check_show_error_ctt) {
                            $('input[name="others_fullname"]').focus();
                        }
                    }
                }

                return isValid;
            }
        }
    }
);