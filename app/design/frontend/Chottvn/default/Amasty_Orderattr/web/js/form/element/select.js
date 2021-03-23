/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package Amasty_Orderattr
 */

define([
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/form/element/abstract',
    'Amasty_Orderattr/js/form/relationAbstract',
    'mage/translate'
], function (ko, _, utils, Select, amastyAbstract, relationAbstract, __) {
    'use strict';

    function indexOptions(data, result) {
        var value;

        result = result || {};
        data.forEach(function (item) {
            value = item.value;
            if (Array.isArray(value)) {
                indexOptions(value, result);
            } else {
                result[value] = item;
            }
        });        
        return result;
    }

    // relationAbstract - attribute dependencies
    return Select.extend(relationAbstract).extend({
        defaults: {
            templates: {
                "checkbox": "ui/form/components/single/checkbox"
            },
            listens: {
                'checked': 'onCheckedChanged'
            }
        },

        initialize: function () {
            this._super();            
            return this;
        },

        setInitialValue: function () {
            this._super();         
            if (this.inputName == 'vat_invoice_required'){   
                this.checked(this.value() == this.getOptionValue("yes"));
            }
            return this;
        },

        initConfig: function (config) {
            this._super();
            if (this.inputName == 'is_vat_invoice_required'
                || this.inputName == 'vat_invoice_required' ){
                this.elementTmpl = this.templates.checkbox;       
            }                  
        },
        initObservable: function () {
            // Observer Check Changed
            return this
                ._super()
                .observe('checked');
        },

        setOptions: function (data) {
            this.indexedOptions = indexOptions(data);
            this.options(data);
            if (_.isFunction(this.caption)) {
                this.caption(false);
            }            
            return this;
        },

        isFieldInvalid: function () {
            return this.error() && this.error().length ? this : null;
        },

        validate: function () {
            /** Required boolean validation(boolean empty value === -1) **/
            var isbooleanRequired = this.dataType === 'boolean'
                && this.required()
                && !this.disabled()
                && this.visible()
                && parseInt(this.value()) === -1,
                result = this._super(),
                message = __('This is a required field.');

            if (isbooleanRequired) {
                this.error(message);
                this.bubble('error', message);

                this.source.set('params.invalid', true);

                return {
                    valid: false,
                    target: this
                };
            }

            return result;
        },

        /**
         * Handle checked state changes for checkbox / radio button.
         *
         * @param {Boolean} newChecked
         * 
         * Assumptions:
         *  Options: 
         *     [0]: default
         *     [1]: Yes
         *     [2]: No
         */
        onCheckedChanged: function (newChecked) {
            var valueYes = this.getOptionValue("yes");
            var valueNo = this.getOptionValue("no");
            
            this.value(newChecked ? valueYes : valueNo);
        },
        getOptionValue: function(valueType){     
            var options = this.options();  
            var value = undefined;  
            if (options.length > 2){   
                switch(valueType) {
                  case "yes":                
                        value = options[1]["value"];                            
                    break;
                  case "no":
                    value = options[2]["value"];
                    break;
                  default:                
                }
            }
            return value;
        }
    });
});
