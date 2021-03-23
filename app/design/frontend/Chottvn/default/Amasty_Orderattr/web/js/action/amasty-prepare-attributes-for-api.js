define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function($, quote) {
    'use strict';

    return function(result, checkoutFormCode) {
        var paymentMethod = quote.paymentMethod();

        var apiResult = {
            'amastyCartId': quote.getQuoteId(),
            'checkoutFormCode': checkoutFormCode,
            'shippingMethodCode': '',
            'entityData': {
                'custom_attributes': []
            }
        };

        if (!quote.isVirtual()) {
            var rate = quote.shippingMethod();

            if (rate && rate.carrier_code && rate.method_code) {
                apiResult.shippingMethodCode = rate.carrier_code + '_' + rate.method_code;
            } else {
                apiResult.shippingMethodCode = 'unknown';
            }
        }
        // if checked 'Yêu cầu xuất hóa đơn'
        var checked_vat_ctt = $('input[name="vat_invoice_required_ctt"]').is(':checked');

        if (checked_vat_ctt) {
            var vat_invoice_required = parseInt($('input[name="vat_invoice_required"]').val());
            vat_invoice_required = vat_invoice_required - 1;
            result.vat_invoice_required = vat_invoice_required.toString();;
        } else {
            result.vat_invoice_required = $('input[name="vat_invoice_required"]').val();
        }
        if (paymentMethod && paymentMethod.method == "cashondelivery") {
            result.bank_transfer_note = '';
        }
        // console.log(paymentMethod);
        // console.log(result);
        _.each(result, function(value, code) {
            if (_.isArray(value)) {
                value = value.join(',');
            }
            apiResult.entityData.custom_attributes.push({
                'attribute_code': code,
                'value': value
            });
        });

        return apiResult;
    }
});