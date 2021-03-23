define([
    'ko',
    'jquery'
], function (ko, $) {
    'use strict';

    var mixin = {
        createRendererComponent: function (address, index) {
            if (address.extension_attributes != undefined && address.extension_attributes.township != undefined) {
                address.township = address.extension_attributes.township;
            }
            this._super(address, index);
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});