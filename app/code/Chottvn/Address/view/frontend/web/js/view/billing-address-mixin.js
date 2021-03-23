define([
    'ko',
    'jquery'
], function (ko, $) {
    'use strict';

    var mixin = {
        defaults: {
            template: 'Chottvn_Address/billing-address'
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});