define([
    'jquery',
    'Magento_Ui/js/form/element/select'
], function($, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            customName: '${ $.parentName }.${ $.index }_input'
        },
        /**
         * Change currently selected option
         *
         * @param {String} id
         */
        selectOption: function(id) {
            // console.log($("#" + id).val());
            $('select[name="transaction_rate"] > option').each(function() {
                // console.log($(this).text() + ' ' + $(this).val());
                if ($(this).val() && ($("#" + id).val() == $(this).val())) {
                    $('input[name="rate"]').val(parseFloat($(this).text())).trigger('change');
                    $('select[name="transaction_rate"]').val($(this).val()).trigger('change');
                    return false;
                }
            });
        },
    });
});