/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'ko',
    'underscore',
    'mageUtils',
    'uiLayout',
    'uiElement',
    'Magento_Ui/js/lib/validation/validator',
    'mage/translate'
], function($, ko, _, utils, layout, Element, validator, $t) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: true,
            preview: '',
            focused: false,
            required: false,
            disabled: false,
            valueChangedByUser: false,
            elementTmpl: 'ui/form/element/input',
            tooltipTpl: 'ui/form/element/helper/tooltip',
            fallbackResetTpl: 'ui/form/element/helper/fallback-reset',
            'input_type': 'input',
            placeholder: false,
            setPlaceholder: '',
            setPlaceholderStreet: $t('Enter the house number, street...'),
            description: '',
            labelVisible: true,
            label: '',
            error: '',
            warn: '',
            notice: '',
            customScope: '',
            default: '',
            isDifferedFromDefault: false,
            showFallbackReset: false,
            additionalClasses: {},
            isUseDefault: '',
            serviceDisabled: false,
            valueUpdate: false, // ko binding valueUpdate

            switcherConfig: {
                component: 'Magento_Ui/js/form/switcher',
                name: '${ $.name }_switcher',
                target: '${ $.name }',
                property: 'value'
            },
            listens: {
                visible: 'setPreview',
                value: 'setDifferedFromDefault',
                '${ $.provider }:data.reset': 'reset',
                '${ $.provider }:data.overload': 'overload',
                '${ $.provider }:${ $.customScope ? $.customScope + "." : ""}data.validate': 'validate',
                'isUseDefault': 'toggleUseDefault'
            },
            ignoreTmpls: {
                value: true
            },

            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },
        color_border_line: '#e0e0e0',

        loadPageNotCheckOthersReceive: ko.observable(false),
        loadPageNotCheckVatRequired: ko.observable(false),

        changeEventCheckbox: function() {
            if (this.index == 'others_receive_products') {
                // if checked 'Người khác nhận hàng'
                var checked = $('input[name="others_receive_products"]').is(':checked');
                if (checked) {
                    $('div.delivery-to-other-customer').show();
                    this.showCustomField();
                } else {
                    $('div.delivery-to-other-customer').hide();
                    this.hideCustomField();
                }
            }
            if (this.index == 'vat_invoice_required_ctt') {
                // if checked 'Yêu cầu xuất hóa đơn'
                var checked_vat_ctt = $('input[name="vat_invoice_required_ctt"]').is(':checked');

                if (checked_vat_ctt) {
                    $('div#check-vat-invoice-required').show();
                    $('input[name="vat_invoice_required"]').prop("checked", true);
                } else {
                    $('div#check-vat-invoice-required').hide();
                    $('input[name="vat_invoice_required"]').prop("checked", false);
                }
            }
            return true;
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function() {
            _.bindAll(this, 'reset');

            this._super()
                .setInitialValue()
                ._setClasses()
                .initSwitcher();

            return this;
        },

        /**
         * Checks if component has error.
         *
         * @returns {Object}
         */
        checkInvalid: function() {
            return this.error() && this.error().length ? this : null;
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Abstract} Chainable.
         */
        initObservable: function() {
            var rules = this.validation = this.validation || {};

            this._super();

            this.observe('error disabled focused preview visible value warn notice isDifferedFromDefault')
                .observe('isUseDefault serviceDisabled')
                .observe({
                    'required': !!rules['required-entry']
                });

            return this;
        },

        /**
         * Initializes regular properties of instance.
         *
         * @returns {Abstract} Chainable.
         */
        initConfig: function() {
            var uid = utils.uniqueid(),
                name,
                valueUpdate,
                scope;

            this._super();

            scope = this.dataScope.split('.');
            name = scope.length > 1 ? scope.slice(1) : scope;

            valueUpdate = this.showFallbackReset ? 'afterkeydown' : this.valueUpdate;

            _.extend(this, {
                uid: uid,
                noticeId: 'notice-' + uid,
                errorId: 'error-' + uid,
                inputName: utils.serializeName(name.join('.')),
                valueUpdate: valueUpdate
            });

            return this;
        },

        /**
         * Initializes switcher element instance.
         *
         * @returns {Abstract} Chainable.
         */
        initSwitcher: function() {
            if (this.switcherConfig.enabled) {
                layout([this.switcherConfig]);
            }

            return this;
        },

        /**
         * Sets initial value of the element and subscribes to it's changes.
         *
         * @returns {Abstract} Chainable.
         */
        setInitialValue: function() {
            if (this.inputName == 'street[0]') {
                this.label = $t('Delivery Address');
            }
            // if (this.index == 'vat_number_ctt') {
            //     this.validation['validate-tax'] = true;
            // }

            // if (this.index == 'vat_contact_phone_number_ctt') {
            //     this.validation['validate-phone-VN-vat'] = true;
            // }

            // if (this.index == 'others_telephone') {
            //     this.validation['validate-phone-VN-others-receive'] = true;
            // }

            this.initialValue = this.getInitialValue(this.index);

            if (this.value.peek() !== this.initialValue) {
                this.value(this.initialValue);
            }
            $.cookie("region_id", null);
            if (this.index == 'region_id') {
                $.cookie("region_id", this.initialValue);
            }

            this.on('value', this.onUpdate.bind(this));
            this.isUseDefault(this.disabled());

            if (this.index == 'others_fullname') {
                this.required._latestValue = true;
            }

            if (this.index == 'others_telephone') {
                this.required._latestValue = true;
            }

            this.setPlaceholder = $t('Input') + ' ' + this.label.toString().toLowerCase();;
            // if (!this.required._latestValue) {
            //     this.setPlaceholder = this.setPlaceholder + ' ' + $t('(optional)');
            // }
            if (this.index == 'affiliate_account_code') {
                this.setPlaceholder = $t('CTV123');
            }
            if (this.index == 'note_shipping') {
                this.setPlaceholder = this.setPlaceholder + ' ' + $t('Notes for delivery staff');
            }
            if (this.index == 'vat_contact_email_ctt') {
                this.setPlaceholder = this.setPlaceholder + ' ' + $t('Enter your email to receive electronic invoices');
            }
            // var isLoggedIn = ko.observable(window.isCustomerLoggedIn),
            //     customerData = {};

            // if (isLoggedIn()) {
            //     customerData = window.customerData;
            //     var group_id = customerData.group_id;

            //     // if (this.index == 'prefix') {
            //     //     // if (this.index == 'prefix' && customerData.prefix != null) {
            //     //     this.disable(true);
            //     //     this.visible(false);
            //     // }
            //     if (this.index == 'firstname_ctt' && this.initialValue != '' && group_id !== "4") {
            //         this.disable(true);
            //         // this.visible(false);
            //     }
            //     if (this.index == "telephone_ctt" && this.initialValue != '' && group_id !== "4") {
            //         this.disable(true);
            //         // this.visible(false);
            //     }
            // }

            return this;
        },

        /**
         * Extends 'additionalClasses' object.
         *
         * @returns {Abstract} Chainable.
         */
        _setClasses: function() {
            var additional = this.additionalClasses;

            if (_.isString(additional)) {
                this.additionalClasses = {};

                if (additional.trim().length) {
                    additional = additional.trim().split(' ');

                    additional.forEach(function(name) {
                        if (name.length) {
                            this.additionalClasses[name] = true;
                        }
                    }, this);
                }
            }

            _.extend(this.additionalClasses, {
                _required: this.required,
                _error: this.error,
                _warn: this.warn,
                _disabled: this.disabled
            });

            return this;
        },

        setDefaultValue: function(index) {
            var isLoggedIn = ko.observable(window.isCustomerLoggedIn),
                customerData = {},
                value = '';

            if (isLoggedIn()) {
                customerData = window.customerData;
                var group_id = customerData.group_id;
                if (index == "firstname_ctt" && group_id !== "4") {
                    value = this.normalizeData(customerData.firstname);
                    if (value !== '') {
                        this.disable(true);
                    }
                }
                if ((index == "telephone_ctt" || index == "telephone") && group_id !== "4") {
                    if (typeof customerData != 'undefined' && typeof customerData.custom_attributes != 'undefined' && typeof customerData.custom_attributes.phone_number != 'undefined') {
                        value = this.normalizeData(customerData.custom_attributes.phone_number.value);
                        if (value !== '') {
                            this.disable(true);
                        }
                    }
                }
                if (index == "email_ctt" && group_id !== "4") {
                    value = this.normalizeData(customerData.email);
                }
                // if (customerData.custom_attributes["affiliate_status"] !== undefined && customerData.custom_attributes["affiliate_code"] !== undefined) {
                //     var affiliate_status = customerData.custom_attributes.affiliate_status.value;
                //     var affiliate_code = customerData.custom_attributes.affiliate_code.value;
                //     if (affiliate_status == 'activated' && affiliate_code !== null && group_id == "4") {
                //         if (index == "affiliate_account_code") {
                //             value = this.normalizeData(affiliate_code);
                //             this.disable(true);
                //             console.log(value);
                //             console.log(this);
                //         }
                //     }
                // }
            }

            return value;
        },

        /**
         * Gets initial value of element
         *
         * @returns {*} Elements' value.
         */
        getInitialValue: function(index) {
            var valueDefault = this.setDefaultValue(index);

            var values = [this.value(), valueDefault ? valueDefault : this.default],
                value;

            values.some(function(v) {
                if (v !== null && v !== undefined) {
                    value = v;

                    return true;
                }

                return false;
            });

            return this.normalizeData(value);
        },

        /**
         * Sets 'value' as 'hidden' property's value, triggers 'toggle' event,
         * sets instance's hidden identifier in params storage based on
         * 'value'.
         *
         * @returns {Abstract} Chainable.
         */
        setVisible: function(isVisible) {
            this.visible(isVisible);

            return this;
        },

        /**
         * Show element.
         *
         * @returns {Abstract} Chainable.
         */
        show: function() {
            this.visible(true);

            return this;
        },

        /**
         * Hide element.
         *
         * @returns {Abstract} Chainable.
         */
        hide: function() {
            this.visible(false);

            return this;
        },

        /**
         * Disable element.
         *
         * @returns {Abstract} Chainable.
         */
        disable: function() {
            this.disabled(true);

            return this;
        },

        /**
         * Enable element.
         *
         * @returns {Abstract} Chainable.
         */
        enable: function() {
            this.disabled(false);

            return this;
        },

        /**
         *
         * @param {(String|Object)} rule
         * @param {(Object|Boolean)} [options]
         * @returns {Abstract} Chainable.
         */
        setValidation: function(rule, options) {
            var rules = utils.copy(this.validation),
                changed;

            if (_.isObject(rule)) {
                _.extend(this.validation, rule);
            } else {
                this.validation[rule] = options;
            }

            changed = !utils.compare(rules, this.validation).equal;

            if (changed) {
                this.required(!!rules['required-entry']);
                this.validate();
            }

            return this;
        },

        /**
         * Returns unwrapped preview observable.
         *
         * @returns {String} Value of the preview observable.
         */
        getPreview: function() {
            return this.value();
        },

        /**
         * Checks if element has addons
         *
         * @returns {Boolean}
         */
        hasAddons: function() {
            return this.addbefore || this.addafter;
        },

        /**
         * Checks if element has service setting
         *
         * @returns {Boolean}
         */
        hasService: function() {
            return this.service && this.service.template;
        },

        /**
         * Defines if value has changed.
         *
         * @returns {Boolean}
         */
        hasChanged: function() {
            // reset phuong xa khi quan huyen null
            if (this.inputName == 'region_id') {
                $('input[name="city"]').val('');
                if (typeof this.value._latestValue == 'undefined') {
                    $('input[name="region"]').val('');

                    $('select[name="custom_attributes[city_id]"]').val('');

                    $('select[name="custom_attributes[township_id]"]').val('');
                    $('input[name="custom_attributes[township]"]').val('');

                    // $('input[name="city"]').val('');
                    $('select[name="city_id"]').val('');

                    $('input[name="township"]').val('');
                    $('select[name="township_id"]').val('');
                }
            }
            if (this.inputName == 'custom_attributes[city_id]' || this.inputName == 'city_id') {
                $('input[name="custom_attributes[township]"]').val('');
                $('input[name="township"]').val('');
                if (typeof this.value._latestValue == 'undefined') {
                    $('input[name="city"]').val('');
                    $('select[name="custom_attributes[township_id]"]').val('');
                    $('select[name="township_id"]').val('');

                    // $('input[name="custom_attributes[township]"]').val('');
                    // $('input[name="township"]').val('');
                }
            }

            // var isLoggedIn = ko.observable(window.isCustomerLoggedIn);

            $('input[name="affiliate_account_code"]').attr('maxlength', '10');

            // if (isLoggedIn() && $('.action.edit-address-link-ctt').hasClass('can-edit-address-link')) {
            //     $('.billing-address-form input[name="region"]').val($('#shipping-new-address-form select[name="region_id"]').val()).trigger("change");
            //     $('.billing-address-form select[name="region_id"]').val($('#shipping-new-address-form select[name="region_id"]').val()).trigger("change");

            //     $('.billing-address-form input[name="city"]').val($('#shipping-new-address-form input[name="city"]').val()).trigger("change");
            //     $('.billing-address-form select[name="city_id"]').val($('#shipping-new-address-form select[name="city_id"]').val()).trigger("change");

            //     $('.billing-address-form input[name="township"]').val($('#shipping-new-address-form input[name="township"]').val()).trigger("change");
            //     $('.billing-address-form select[name="township_id"]').val($('#shipping-new-address-form select[name="township_id"]').val()).trigger("change");

            //     $('.billing-address-form input[name="street[0]"]').val($('#shipping-new-address-form input[name="street[0]"]').val()).trigger("change");
            // }
            // if (!isLoggedIn()) {
            //     $('input[name="region"]').val($('input[name="region"]').val()).trigger("change");
            //     $('select[name="region_id"]').val($('select[name="region_id"]').val()).trigger("change");
            //     $('input[name="city"]').val($('input[name="city"]').val()).trigger("change");
            //     $('select[name="custom_attributes[city_id]"]').val($('select[name="custom_attributes[city_id]"]').val()).trigger("change");
            //     $('input[name="custom_attributes[township]"]').val($('input[name="custom_attributes[township]"]').val()).trigger("change");
            //     $('select[name="custom_attributes[township_id]"]').val($('select[name="custom_attributes[township_id]"]').val()).trigger("change");
            //     $('input[name="street[0]"]').val($('input[name="street[0]"]').val()).trigger("change");
            // }

            $('input[name="telephone_ctt"]').keyup(function() {
                $('input[name="telephone"]').val($('input[name="telephone_ctt"]').val()).trigger("change");
            });
            var check_vat = $('input[name="vat_invoice_required_ctt"]').is(':checked'),
                div_vat = document.getElementById('checkout-vat-invoice-required');

            if (check_vat) {
                $(div_vat).find('input:text')
                    .each(function() {
                        // if ($(this).attr('name') !== 'vat_contact_phone_number_ctt') {
                        if ($(this).val().trim() !== '') {
                            $(this).css('border-color', '#e0e0e0');
                        }
                        // }
                    });
            }
            var check_others_receive_products = $('input[name="others_receive_products"]').is(':checked'),
                div_customer_info = document.getElementById('customer-checkout-step-info');

            $(div_customer_info).find('input:text')
                .each(function() {
                    if ($(this).val().trim() !== '') {
                        if (!check_others_receive_products &&
                            $(this).attr('name') !== 'others_fullname' &&
                            $(this).attr('name') !== 'others_telephone' &&
                            $(this).attr('name') !== 'others_email') {
                            $(this).css('border-color', '#e0e0e0');
                        }
                        if (check_others_receive_products) {
                            $(this).css('border-color', '#e0e0e0');
                        }
                    } else {
                        if ($(this).attr('name') !== 'email_ctt' || $(this).attr('name') !== 'others_email') {
                            $(this).css('border-color', '#e0e0e0');
                        }
                    }
                });

            var region_ = $('input[name="region"]').val(),
                regionId_ = $('select[name="region_id"]').val(),
                city_ = $('input[name="city"]').val(),
                city_id_ = $('select[name="custom_attributes[city_id]"]').val(),
                township_ = $('input[name="custom_attributes[township]"]').val(),
                township_id_ = $('select[name="custom_attributes[township_id]"]').val(),
                street_ = $('input[name="street[0]"]').val(),
                city__ = $('#co-shipping-form input[name="city"]').val(),
                cityId_ = $('#co-shipping-form select[name="city_id"]').val(),
                township__ = $('#co-shipping-form input[name="township"]').val(),
                townshipId_ = $('#co-shipping-form select[name="township_id"]').val(),
                street__ = $('#co-shipping-form input[name="street[0]"]').val();

            if (!utils.isEmpty(region_) || !utils.isEmpty(regionId_)) {
                $('input[name="region"]').css('border-color', '#e0e0e0');
                $('select[name="region_id"]').css('border-color', '#e0e0e0');
            }
            if (!utils.isEmpty(city_) || !utils.isEmpty(city_id_)) {
                $('input[name="city"]').css('border-color', '#e0e0e0');
                $('select[name="custom_attributes[city_id]"]').css('border-color', '#e0e0e0');
            }
            if (!utils.isEmpty(township_) || !utils.isEmpty(township_id_)) {
                $('input[name="custom_attributes[township]"]').css('border-color', '#e0e0e0');
                $('select[name="custom_attributes[township_id]"]').css('border-color', '#e0e0e0');
            }
            if (!utils.isEmpty(street_)) {
                $('input[name="street[0]"]').css('border-color', '#e0e0e0');
            }
            if (!utils.isEmpty(city__) || !utils.isEmpty(cityId_)) {
                $('#co-shipping-form input[name="city"]').css('border-color', '#e0e0e0');
                $('#co-shipping-form select[name="city_id"]').css('border-color', '#e0e0e0');
            }
            if (!utils.isEmpty(township__) || !utils.isEmpty(townshipId_)) {
                $('#co-shipping-form input[name="township"]').css('border-color', '#e0e0e0');
                $('#co-shipping-form select[name="township_id"]').css('border-color', '#e0e0e0');
            }
            if (!utils.isEmpty(street__)) {
                $('#co-shipping-form input[name="street[0]"]').css('border-color', '#e0e0e0');
            }

            var notEqual = this.value() !== this.initialValue;

            return !this.visible() ? false : notEqual;
        },

        showCustomField: function() {
            $('div[name="shippingAddress.others_email"]').show();
            $('div[name="shippingAddress.others_fullname"]').show();
            $('div[name="shippingAddress.others_telephone"]').show();
        },

        hideCustomField: function() {
            $('div[name="shippingAddress.others_email"]').hide();
            $('div[name="shippingAddress.others_fullname"]').hide();
            $('div[name="shippingAddress.others_telephone"]').hide();
        },

        /**
         * Checks if 'value' is not empty.
         *
         * @returns {Boolean}
         */
        hasData: function() {
            return !utils.isEmpty(this.value());
        },

        /**
         * Sets value observable to initialValue property.
         *
         * @returns {Abstract} Chainable.
         */
        reset: function() {
            this.value(this.initialValue);
            this.error(false);

            return this;
        },

        /**
         * Sets current state as initial.
         */
        overload: function() {
            this.setInitialValue();
            this.bubble('update', this.hasChanged());
        },

        /**
         * Clears 'value' property.
         *
         * @returns {Abstract} Chainable.
         */
        clear: function() {
            this.value('');

            return this;
        },

        /**
         * Converts values like 'null' or 'undefined' to an empty string.
         *
         * @param {*} value - Value to be processed.
         * @returns {*}
         */
        normalizeData: function(value) {
            return utils.isEmpty(value) ? '' : value;
        },

        /**
         * Validates itself by it's validation rules using validator object.
         * If validation of a rule did not pass, writes it's message to
         * 'error' observable property.
         *
         * @returns {Object} Validate information.
         */
        validate: function() {
            if (this.inputName == 'region_id' ||
                this.inputName == 'region' ||
                this.inputName == 'custom_attributes[city_id]' ||
                this.inputName == 'city' ||
                this.inputName == 'city_id' ||
                this.inputName == 'custom_attributes[township_id]' ||
                this.inputName == 'street[0]' ||
                this.inputName == 'custom_attributes[township]' ||
                this.inputName == 'firstname_ctt' ||
                this.inputName == 'telephone_ctt' ||
                this.inputName == 'email_ctt' ||
                this.inputName == 'others_fullname' ||
                this.inputName == 'others_telephone' ||
                this.inputName == 'others_email') {
                return {
                    valid: true,
                    target: this
                };
            }
            var value = this.value(),
                result = validator(this.validation, value, this.validationParams),
                message = !this.disabled() && this.visible() ? result.message : '',
                isValid = this.disabled() || !this.visible() || result.passed;

            this.error(message);
            this.error.valueHasMutated();
            this.bubble('error', message);

            //TODO: Implement proper result propagation for form
            if (this.source && !isValid) {
                this.source.set('params.invalid', true);
            }

            return {
                valid: isValid,
                target: this
            };
        },

        /**
         * Callback that fires when 'value' property is updated.
         */
        onUpdate: function() {
            this.bubble('update', this.hasChanged());

            this.validate();
        },

        /**
         * Restore value to default
         */
        restoreToDefault: function() {
            this.value(this.default);
            this.focused(true);
        },

        /**
         * Update whether value differs from default value
         */
        setDifferedFromDefault: function() {
            var value = typeof this.value() != 'undefined' && this.value() !== null ? this.value() : '',
                defaultValue = typeof this.default != 'undefined' && this.default !== null ? this.default : '';

            this.isDifferedFromDefault(value !== defaultValue);
        },

        /**
         * @param {Boolean} state
         */
        toggleUseDefault: function(state) {
            this.disabled(state);

            if (this.source && this.hasService()) {
                this.source.set('data.use_default.' + this.index, Number(state));
            }
        },

        /**
         *  Callback when value is changed by user
         */
        userChanges: function() {
            this.valueChangedByUser = true;
        },

        /**
         * Returns correct id for 'aria-describedby' accessibility attribute
         *
         * @returns {Boolean|String}
         */
        getDescriptionId: function() {
            var id = false;

            if (this.error()) {
                id = this.errorId;
            } else if (this.notice()) {
                id = this.noticeId;
            }

            return id;
        }
    });
});