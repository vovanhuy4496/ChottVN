/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/totals',
    'mageUtils',
    'ko',
], function($, Component, totals, utils, ko) {
    'use strict';

    return Component.extend({
        flag: false,
        isLoading: totals.isLoading,
        _isLoading: function() {
            if (!this.flag) {
                this.flag = true;
                return this.isLoading();
            }
            return false;
        },
        /**
         * add loader block for summary
         */
        // isLoading: function() {
        //     var region_ = $('input[name="region"]').val(),
        //         regionId_ = $('select[name="region_id"]').val(),
        //         city_ = $('input[name="custom_attributes[city]"]').val(),
        //         city_id_ = $('select[name="custom_attributes[city_id]"]').val(),
        //         township_ = $('input[name="custom_attributes[township]"]').val(),
        //         township_id_ = $('select[name="custom_attributes[township_id]"]').val(),
        //         street_ = $('input[name="street[0]"]').val();
        //     if (utils.isEmpty(city_id_) && utils.isEmpty(township_) && utils.isEmpty(street_)) {
        //         return totals.isLoading;
        //     }
        //     if (!utils.isEmpty(street_)) {
        //         if (!utils.isEmpty(regionId_) &&
        //             !utils.isEmpty(city_id_) &&
        //             (!utils.isEmpty(township_) || !utils.isEmpty(township_id_))) {
        //             return totals.isLoading;
        //         }
        //     }
        //     var isLoading = ko.observable(false);
        //     return isLoading;
        // }
    });
});