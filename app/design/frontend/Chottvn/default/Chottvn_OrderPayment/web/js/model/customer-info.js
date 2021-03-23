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
                    check_others_receive_products = $('input[name="others_receive_products"]').is(':checked'),
                    isLoggedIn = ko.observable(window.isCustomerLoggedIn),
                    div_customer_info = document.getElementById('customer-checkout-step-info');

                $(div_customer_info).find('input:text')
                    .each(function() {
                        $(this).val($.trim($(this).val()));
                        // if ($(this).attr('name') == 'telephone_ctt') {
                        //     $(this).val($.trim($(this).val()));
                        // }
                        // if (check_others_receive_products) {
                        //     if ($(this).attr('name') == 'others_telephone') {
                        //         $(this).val($.trim($(this).val()));
                        //     }
                        // }
                        if ($(this).val().trim() == '' && $(this).attr('name') !== 'email_ctt' && $(this).attr('name') !== 'others_email') {
                            if (!check_others_receive_products &&
                                $(this).attr('name') !== 'others_fullname' &&
                                $(this).attr('name') !== 'others_telephone') {
                                isValid = false;
                                $(this).css('border-color', '#ff6000');
                            }
                            if (check_others_receive_products) {
                                isValid = false;
                                $(this).css('border-color', '#ff6000');
                            }
                        }
                    });
                if (!isValid) {
                    $.toaster({ title: $t('Customer Info'), priority: 'danger', message: $t('Please complete') + ' ' + $t('Customer Info') });
                } else {
                    // console.log(check_others_receive_products);
                    var validPhoneNumber = false;
                    var validEmail = false;

                    // if (!check_others_receive_products) {
                    // }
                    if (check_others_receive_products && isValid) {
                        if (!this.validateEmail($('input[name="others_email"]').val()) && !utils.isEmpty($('input[name="others_email"]').val())) {
                            $('input[name="others_email"]').focus();
                            $('input[name="others_email"]').css('border-color', '#ff6000');
                            // $.toaster({ title: $t('Customer Info'), priority: 'danger', message: $t('Please enter a valid email address (Ex: johndoe@domain.com).') });
                            validEmail = true;
                            isValid = false;
                        }
                        if (!this.validatePhone($('input[name="others_telephone"]').val())) {
                            isValid = false;
                            $('input[name="others_telephone"]').css('border-color', '#ff6000');
                            $('input[name="others_telephone"]').focus();
                            validPhoneNumber = true;
                            // $.toaster({ title: $t('Customer Info'), priority: 'danger', message: $t('Please enter a valid phone number. For example 0899002022.') });
                        }
                    }
                    if (!this.validateEmail($('input[name="email_ctt"]').val()) && !utils.isEmpty($('input[name="email_ctt"]').val())) {
                        $('input[name="email_ctt"]').focus();
                        $('input[name="email_ctt"]').css('border-color', '#ff6000');
                        // $.toaster({ title: $t('Customer Info'), priority: 'danger', message: $t('Please enter a valid email address (Ex: johndoe@domain.com).') });
                        validEmail = true;
                        isValid = false;
                    }
                    if (!this.validatePhone($('input[name="telephone_ctt"]').val())) {
                        isValid = false;
                        $('input[name="telephone_ctt"]').css('border-color', '#ff6000');
                        $('input[name="telephone_ctt"]').focus();
                        // $.toaster({ title: $t('Customer Info'), priority: 'danger', message: $t('Please enter a valid phone number. For example 0899002022.') });
                        validPhoneNumber = true;
                    }
                    if (validPhoneNumber) {
                        $.toaster({ title: $t('Customer Info'), priority: 'danger', message: $t('Please enter a valid phone number. For example 0899002022.') });
                    }
                    if (validEmail) {
                        $.toaster({ title: $t('Customer Info'), priority: 'danger', message: $t('Please enter a valid email address (Ex: johndoe@domain.com).') });
                    }
                }

                return isValid;
            },

            validatePhone: function(value) {
                return $.mage.isEmptyNoTrim(value) || (value.length > 9 &&
                    /^(0)\d{9}$|^(84)\d{9}$/.test(value));
            },

            validateEmail: function(value) {
                return /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value); //eslint-disable-line max-len
            }
        }
    });