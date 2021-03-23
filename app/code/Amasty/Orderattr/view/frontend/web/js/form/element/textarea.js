define([
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/form/element/textarea',
    'Amasty_Orderattr/js/form/relationAbstract'
], function (ko, _, utils, TextArea, relationAbstract) {
    'use strict';

    return TextArea.extend(relationAbstract).extend({
        isFieldInvalid: function () {
            return this.error() && this.error().length ? this : null;
        }
    });
});
