/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package Amasty_Orderattr
 */

define([
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/form/element/multiselect',
    'Amasty_Orderattr/js/form/relationAbstract'
], function (ko, _, utils, Multiselect, relationAbstract) {
    'use strict';

    return Multiselect.extend(relationAbstract).extend({
        isFieldInvalid: function () {
            return this.error() && this.error().length ? this : null;
        }
    });
});
