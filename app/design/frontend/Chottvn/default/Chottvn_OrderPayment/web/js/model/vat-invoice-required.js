define(
    [
        'jquery',
        'ko',
        'mage/translate',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/quote',
        'mageUtils',
        'jquery/ui',
        'jquery/validate',
    ],
    function($, ko, $t, messageList, quote, utils) {
        'use strict';
        return {
            validate: function() {
                var isValid = true,
                    check_vat = $('input[name="vat_invoice_required_ctt"]').is(':checked'),
                    isLoggedIn = ko.observable(window.isCustomerLoggedIn),
                    div_vat = document.getElementById('checkout-vat-invoice-required');

                if (check_vat) {
                    $(div_vat).find('input:text')
                        .each(function() {
                            $(this).val($.trim($(this).val()));
                            if ($(this).attr('name') !== 'vat_contact_phone_number_ctt') {
                                if ($(this).val().trim() == '') {
                                    isValid = false;
                                    $(this).css('border-color', '#ff6000');
                                }
                            }
                        });
                }
                if (!isValid) {
                    $.toaster({ title: $t('Invoice information'), priority: 'danger', message: $t('Please complete') + ' ' + $t('Invoice information') });
                } else {
                    // console.log(check_vat);
                    if (check_vat) {
                        if (!this.validateEmail($('input[name="vat_contact_email_ctt"]').val()) && !utils.isEmpty($('input[name="vat_contact_email_ctt"]').val())) {
                            $('input[name="vat_contact_email_ctt"]').focus();
                            $('input[name="vat_contact_email_ctt"]').css('border-color', '#ff6000');
                            $.toaster({ title: $t('Invoice information'), priority: 'danger', message: $t('Please enter a valid email address (Ex: johndoe@domain.com).') });
                            return isValid = false;
                        }
                        if (!this.validatePhone($('input[name="vat_contact_phone_number_ctt"]').val())) {
                            $('input[name="vat_contact_phone_number_ctt"]').css('border-color', '#ff6000');
                            $('input[name="vat_contact_phone_number_ctt"]').focus();
                            $.toaster({ title: $t('Invoice information'), priority: 'danger', message: $t('Please enter a valid phone number. For example 0899002022.') });
                            return isValid = false;
                        }
                        if (!this.validateTax($('input[name="vat_number_ctt"]').val())) {
                            $('input[name="vat_number_ctt"]').css('border-color', '#ff6000');
                            $('input[name="vat_number_ctt"]').focus();
                            $.toaster({ title: $t('Invoice information'), priority: 'danger', message: $t('Please enter a valid fax number (Ex: 1234567890 or 1234567890-123).') });
                            return isValid = false;
                        }
                    }
                }
                // console.log(isValid);

                return isValid;
            },

            validateTax: function(value) {
                return $.mage.isEmptyNoTrim(value) || /^\d{10}$|^\d{10}(-)\d{3}$/.test(value);
            },

            validatePhone: function(value) {
                return $.mage.isEmptyNoTrim(value) || (value.length > 9 &&
                    /^(0)\d{9}$|^(84)\d{9}$/.test(value));
            },

            validateEmail: function(value) {
                return /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value); //eslint-disable-line max-len
            }
        }
    }
);