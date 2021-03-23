define([
    'ko',
    'jquery'
], function (ko, $) {
    'use strict';

    var mixin = {
        defaults: {
            template: 'Chottvn_Address/shipping-address/address-renderer/default'
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});