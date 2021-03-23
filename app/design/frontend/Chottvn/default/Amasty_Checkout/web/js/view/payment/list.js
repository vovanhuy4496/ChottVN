define([
    'jquery',
    'Magento_Checkout/js/model/payment-service',
    'mageUtils',
    'ko',
], function($, paymentService, utils, ko) {
    'use strict';

    return function(Component) {
        return Component.extend({
            flag: false,
            isLoading: paymentService.isLoading,
            /**
             * add loader block for payment
             */
            // isLoading: paymentService.isLoading,
            // _isLoading: function() {
            //     console.log(this.flag);
            //     var region_ = $('input[name="region"]').val(),
            //         regionId_ = $('select[name="region_id"]').val(),
            //         city_ = $('input[name="custom_attributes[city]"]').val(),
            //         city_id_ = $('select[name="custom_attributes[city_id]"]').val(),
            //         township_ = $('input[name="custom_attributes[township]"]').val(),
            //         township_id_ = $('select[name="custom_attributes[township_id]"]').val(),
            //         street_ = $('input[name="street[0]"]').val();
            //     if (!this.flag && utils.isEmpty(city_id_) &&
            //         (utils.isEmpty(township_) || utils.isEmpty(township_id_)) &&
            //         utils.isEmpty(street_)) {
            //         this.flag = true;
            //         return this.isLoading();
            //     }
            //     if (!utils.isEmpty(street_)) {
            //         if (!utils.isEmpty(regionId_) &&
            //             !utils.isEmpty(city_id_) &&
            //             (!utils.isEmpty(township_) || !utils.isEmpty(township_id_))) {
            //             console.log(this.flag);
            //             return this.isLoading();
            //             // return paymentService.isLoading;
            //         }
            //     }
            //     // var isLoading = ko.observable(false);
            //     return false;
            // },
            _isLoading: function() {
                if (!this.flag) {
                    this.flag = true;
                    return this.isLoading();
                }
                return false;
            },

            getGroupTitle: function(newValue) {
                if (newValue().index === 'methodGroup' &&
                    window.checkoutConfig.quoteData.block_info.block_payment_method
                ) {
                    return window.checkoutConfig.quoteData.block_info.block_payment_method['value'];
                }

                return this._super(newValue);
            }
        });
    };
});