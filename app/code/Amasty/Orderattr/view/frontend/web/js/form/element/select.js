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
        }
    });
});
